<?php

namespace Tests\Feature\Admin;

use App\Models\Brand;
use App\Models\Upload;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrandTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@wowmart.test')->firstOrFail();
    }

    public function test_the_list_searches_by_name_and_shows_product_counts(): void
    {
        $arduino = Brand::where('name', 'Arduino')->firstOrFail();

        $this->actingAs($this->admin)->get('/admin/brands?search=ardu')
            ->assertOk()
            ->assertSee('Arduino')
            ->assertDontSee('Kingston')
            ->assertViewHas('brands', fn ($page) => $page->first()->products_count === $arduino->products()->count())
            ->assertSee('Add a brand');
    }

    public function test_a_brand_is_added_from_the_list_page(): void
    {
        $logo = Upload::where('type', 'image')->firstOrFail();

        $this->actingAs($this->admin)
            ->from('/admin/brands')
            ->post('/admin/brands', ['name' => 'Adafruit', 'logo' => $logo->id, 'top' => '1', 'meta_title' => 'Adafruit parts'])
            ->assertRedirect('/admin/brands')
            ->assertSessionHas('status');

        $brand = Brand::where('name', 'Adafruit')->firstOrFail();
        $this->assertSame('adafruit', $brand->slug);
        $this->assertSame($logo->id, $brand->logo);
        $this->assertTrue($brand->top);
    }

    public function test_an_unticked_top_box_clears_the_flag_on_edit(): void
    {
        $brand = Brand::where('name', 'Kingston')->firstOrFail();
        $brand->forceFill(['top' => true])->save();

        $this->actingAs($this->admin)->put("/admin/brands/{$brand->id}", ['name' => 'Kingston'])->assertSessionHasNoErrors();

        $this->assertFalse($brand->fresh()->top);
    }

    public function test_an_array_search_is_ignored(): void
    {
        $this->actingAs($this->admin)->get('/admin/brands?search[]=x')->assertOk()->assertSee('Kingston');
    }

    public function test_a_duplicate_name_is_refused_in_any_case(): void
    {
        $this->actingAs($this->admin)
            ->from('/admin/brands')
            ->post('/admin/brands', ['name' => 'aRDUINO'])
            ->assertRedirect('/admin/brands')
            ->assertSessionHasErrors('name');

        $this->assertSame(1, Brand::whereRaw('lower(name) = ?', ['arduino'])->count());
    }

    public function test_a_duplicate_non_ascii_name_is_refused(): void
    {
        $this->actingAs($this->admin)->post('/admin/brands', ['name' => 'Élan'])->assertSessionHasNoErrors();
        $this->actingAs($this->admin)->post('/admin/brands', ['name' => 'Élan'])->assertSessionHasErrors('name');

        $this->assertSame(1, Brand::where('name', 'Élan')->count());
    }

    public function test_the_form_rejects_bad_input(): void
    {
        $this->actingAs($this->admin)
            ->post('/admin/brands', ['name' => str_repeat('x', 101), 'logo' => 999999])
            ->assertSessionHasErrors(['name', 'logo']);

        $this->actingAs($this->admin)->post('/admin/brands', ['name' => ''])->assertSessionHasErrors('name');
    }

    public function test_renaming_keeps_the_slug_and_its_own_name_is_not_a_duplicate(): void
    {
        $brand = Brand::where('name', 'LEGO')->firstOrFail();

        $this->actingAs($this->admin)->get("/admin/brands/{$brand->id}/edit")->assertOk()->assertSee('Save changes');

        // Same name, different case: it is this brand, not a clash.
        $this->actingAs($this->admin)->put("/admin/brands/{$brand->id}", ['name' => 'Lego'])->assertSessionHasNoErrors();

        $this->actingAs($this->admin)
            ->put("/admin/brands/{$brand->id}", ['name' => 'LEGO Education', 'meta_description' => 'Bricks'])
            ->assertRedirect("/admin/brands/{$brand->id}/edit");

        $brand->refresh();
        $this->assertSame('LEGO Education', $brand->name);
        $this->assertSame('lego', $brand->slug);
        $this->assertSame('Bricks', $brand->meta_description);
    }

    public function test_renaming_onto_another_brand_is_refused(): void
    {
        $brand = Brand::where('name', 'LEGO')->firstOrFail();

        $this->actingAs($this->admin)->put("/admin/brands/{$brand->id}", ['name' => 'kingston'])->assertSessionHasErrors('name');
    }

    public function test_a_brand_in_use_cannot_be_deleted(): void
    {
        $brand = Brand::where('name', 'Arduino')->firstOrFail();
        $count = $brand->products()->count();
        $this->assertGreaterThan(0, $count);

        $this->actingAs($this->admin)
            ->from('/admin/brands')
            ->delete("/admin/brands/{$brand->id}")
            ->assertRedirect('/admin/brands')
            ->assertSessionHas('error', fn (string $m) => str_starts_with($m, "{$count} products use this brand"));

        $this->assertModelExists($brand);
    }

    public function test_an_unused_brand_is_deleted(): void
    {
        $brand = (new Brand(['name' => 'Unused']))->forceFill(['slug' => 'unused']);
        $brand->save();

        $this->actingAs($this->admin)->delete("/admin/brands/{$brand->id}")->assertRedirect('/admin/brands');

        $this->assertModelMissing($brand);
    }

    public function test_the_top_switch_flips_the_flag(): void
    {
        $brand = Brand::where('name', 'Kingston')->firstOrFail();
        $this->assertFalse($brand->top);

        $this->actingAs($this->admin)->post("/admin/brands/{$brand->id}/toggle/top")->assertSessionHas('status');
        $this->assertTrue($brand->fresh()->top);

        $this->actingAs($this->admin)->post("/admin/brands/{$brand->id}/toggle/top");
        $this->assertFalse($brand->fresh()->top);

        $this->actingAs($this->admin)->post("/admin/brands/{$brand->id}/toggle/name")->assertNotFound();
    }
}
