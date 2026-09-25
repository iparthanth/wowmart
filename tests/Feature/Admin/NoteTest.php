<?php

namespace Tests\Feature\Admin;

use App\Models\Note;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoteTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@wowmart.test')->firstOrFail();
    }

    private function note(string $type): Note
    {
        return Note::where('type', $type)->firstOrFail();
    }

    public function test_the_list_filters_by_type_and_counts_products(): void
    {
        $refund = $this->note('refund');
        Product::orderBy('id')->take(3)->get()->each(fn ($p) => $p->forceFill(['refund_note_id' => $refund->id])->save());

        $this->actingAs($this->admin)->get('/admin/note')
            ->assertOk()
            ->assertSee('Add new note')
            ->assertViewHas('notes', fn ($page) => $page->total() === 2
                && $page->firstWhere('type', 'refund')->productsCount() === 3
                && $page->firstWhere('type', 'warranty')->productsCount() === 0);

        $this->actingAs($this->admin)->get('/admin/note?type=warranty')
            ->assertOk()
            ->assertSee('Covers manufacturing defects')
            ->assertDontSee('Unused items in their original packaging');

        // Unknown or malformed filters are ignored, not rejected.
        foreach (['?type=other', '?type[]=refund'] as $query) {
            $this->actingAs($this->admin)->get('/admin/note'.$query)
                ->assertOk()->assertViewHas('notes', fn ($page) => $page->total() === 2);
        }
    }

    public function test_the_list_shortens_long_notes_to_160_characters(): void
    {
        Note::create(['type' => 'refund', 'description' => str_repeat('x', 170).'TAIL']);

        $this->actingAs($this->admin)->get('/admin/note')
            ->assertOk()->assertSee(str_repeat('x', 160).'...')->assertDontSee('TAIL');
    }

    public function test_a_note_is_created(): void
    {
        $this->actingAs($this->admin)->get('/admin/note/create')->assertOk()->assertSee('New note');

        $this->actingAs($this->admin)->post('/admin/note', ['type' => 'warranty', 'description' => 'Two years on the battery.'])
            ->assertRedirect('/admin/note')->assertSessionHasNoErrors();

        $this->assertSame('warranty', Note::where('description', 'Two years on the battery.')->firstOrFail()->type);
    }

    public function test_type_and_description_are_validated(): void
    {
        $this->actingAs($this->admin)->post('/admin/note', ['type' => 'shipping', 'description' => 'x'])->assertSessionHasErrors('type');
        $this->actingAs($this->admin)->post('/admin/note', ['type' => ['refund'], 'description' => 'x'])->assertSessionHasErrors('type');
        $this->actingAs($this->admin)->post('/admin/note', ['type' => 'refund', 'description' => str_repeat('a', 5001)])->assertSessionHasErrors('description');
        $this->actingAs($this->admin)->post('/admin/note', ['type' => 'refund'])->assertSessionHasErrors('description');

        $this->assertSame(2, Note::count());
    }

    public function test_the_type_cannot_change_after_creation(): void
    {
        $refund = $this->note('refund');

        $this->actingAs($this->admin)->put("/admin/note/{$refund->id}", ['type' => 'warranty', 'description' => 'Changed'])
            ->assertSessionHasErrors(['type' => 'A note keeps its type. Add a new note for the other type instead.']);
        $this->assertSame('refund', $refund->fresh()->type);
        $this->assertNotSame('Changed', $refund->fresh()->description);

        // The edit form does not post the type at all.
        $this->actingAs($this->admin)->put("/admin/note/{$refund->id}", ['description' => '14 days to return.'])
            ->assertRedirect("/admin/note/{$refund->id}/edit")->assertSessionHasNoErrors();
        $this->assertSame('refund', $refund->fresh()->type);
        $this->assertSame('14 days to return.', $refund->fresh()->description);

        $this->actingAs($this->admin)->get("/admin/note/{$refund->id}/edit")->assertOk()->assertSee('A note keeps its type.');
    }

    public function test_a_note_in_use_cannot_be_deleted(): void
    {
        $warranty = $this->note('warranty');
        Product::orderBy('id')->firstOrFail()->forceFill(['warranty_note_id' => $warranty->id])->save();

        $this->actingAs($this->admin)->from('/admin/note')->delete("/admin/note/{$warranty->id}")
            ->assertRedirect('/admin/note')
            ->assertSessionHas('error', '1 product uses this note; pick another note on it first.');
        $this->assertNotNull($warranty->fresh());

        $refund = $this->note('refund');
        Product::orderBy('id')->skip(1)->take(2)->get()->each(fn ($p) => $p->forceFill(['refund_note_id' => $refund->id])->save());
        $this->actingAs($this->admin)->delete("/admin/note/{$refund->id}")
            ->assertSessionHas('error', '2 products use this note; pick another note on them first.');
    }

    public function test_an_unused_note_is_deleted(): void
    {
        $refund = $this->note('refund');

        $this->actingAs($this->admin)->delete("/admin/note/{$refund->id}")
            ->assertRedirect('/admin/note')->assertSessionHas('status');

        $this->assertNull($refund->fresh());
        $this->assertSame(1, Note::count());
    }
}
