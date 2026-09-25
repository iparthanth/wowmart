<?php

namespace Tests\Feature\Admin;

use App\Models\Product;
use App\Models\User;
use App\Models\Warranty;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WarrantyTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@wowmart.test')->firstOrFail();
    }

    private function warranty(string $text): Warranty
    {
        return Warranty::where('text', $text)->firstOrFail();
    }

    public function test_the_list_shows_product_counts(): void
    {
        Product::orderBy('id')->take(2)->get()->each(fn ($p) => $p->forceFill(['warranty_id' => $this->warranty('1 year')->id])->save());

        $this->actingAs($this->admin)->get('/admin/warranties')
            ->assertOk()
            ->assertSee('Add a warranty')
            ->assertViewHas('warranties', fn ($page) => $page->total() === 3
                && $page->firstWhere('text', '1 year')->products_count === 2
                && $page->firstWhere('text', '2 years')->products_count === 0);
    }

    public function test_a_warranty_is_added_and_renamed(): void
    {
        $this->actingAs($this->admin)->post('/admin/warranties', ['text' => '3 months replacement'])
            ->assertRedirect('/admin/warranties')->assertSessionHasNoErrors();

        $w = $this->warranty('3 months replacement');
        $this->actingAs($this->admin)->put("/admin/warranties/{$w->id}", ['text' => '3 months'])
            ->assertRedirect("/admin/warranties/{$w->id}/edit");
        $this->assertSame('3 months', $w->fresh()->text);

        $this->actingAs($this->admin)->get("/admin/warranties/{$w->id}/edit")->assertOk()->assertSee('3 months');
    }

    public function test_the_text_is_unique_in_any_case_and_limited_to_100(): void
    {
        $this->actingAs($this->admin)->post('/admin/warranties', ['text' => '1 YEAR'])->assertSessionHasErrors('text');
        $this->actingAs($this->admin)->post('/admin/warranties', ['text' => str_repeat('a', 101)])->assertSessionHasErrors('text');
        $this->assertSame(3, Warranty::count());

        $year = $this->warranty('1 year');
        $this->actingAs($this->admin)->put("/admin/warranties/{$year->id}", ['text' => '2 Years'])->assertSessionHasErrors('text');
        $this->actingAs($this->admin)->put("/admin/warranties/{$year->id}", ['text' => '1 Year'])->assertSessionHasNoErrors();
    }

    public function test_a_warranty_in_use_cannot_be_deleted(): void
    {
        $year = $this->warranty('1 year');
        $product = Product::orderBy('id')->firstOrFail();
        $product->forceFill(['warranty_id' => $year->id])->save();

        $this->actingAs($this->admin)->from('/admin/warranties')->delete("/admin/warranties/{$year->id}")
            ->assertRedirect('/admin/warranties')
            ->assertSessionHas('error', '1 product uses this warranty; pick another warranty on it first.');

        $this->assertNotNull($year->fresh());
        $this->assertSame($year->id, $product->fresh()->warranty_id);
    }

    public function test_an_unused_warranty_is_deleted(): void
    {
        $w = $this->warranty('6 months');

        $this->actingAs($this->admin)->delete("/admin/warranties/{$w->id}")
            ->assertRedirect('/admin/warranties')->assertSessionHas('status');

        $this->assertNull($w->fresh());
        $this->assertSame(2, Warranty::count());
    }
}
