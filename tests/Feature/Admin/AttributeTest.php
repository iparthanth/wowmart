<?php

namespace Tests\Feature\Admin;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttributeTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@wowmart.test')->firstOrFail();
    }

    private function attribute(string $name): Attribute
    {
        return Attribute::where('name', $name)->firstOrFail();
    }

    private function value(string $attribute, string $value): AttributeValue
    {
        return $this->attribute($attribute)->values()->where('value', $value)->firstOrFail();
    }

    public function test_the_list_shows_value_counts_and_searches(): void
    {
        $this->actingAs($this->admin)->get('/admin/attributes')
            ->assertOk()
            ->assertSee('Add an attribute')
            ->assertSeeInOrder(['Capacity', '128GB', 'Length', '1m', 'Size', 'Large'])
            ->assertViewHas('attributes', fn ($page) => $page->total() === 3 && $page->every(fn ($a) => $a->values_count === 3));

        $this->actingAs($this->admin)->get('/admin/attributes?search=cap')
            ->assertOk()
            ->assertViewHas('attributes', fn ($page) => $page->pluck('name')->all() === ['Capacity']);

        $this->actingAs($this->admin)->get('/admin/attributes?search[]=x')->assertOk()->assertSee('Length');
    }

    public function test_an_attribute_is_added_and_opens_on_its_values(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/attributes', ['name' => 'Voltage']);

        $voltage = $this->attribute('Voltage');
        $response->assertRedirect("/admin/attributes/{$voltage->id}/edit")->assertSessionHas('status');
        $this->actingAs($this->admin)->get("/admin/attributes/{$voltage->id}/edit")->assertOk()->assertSee('No values yet');
    }

    public function test_attribute_names_are_unique_in_any_case(): void
    {
        $this->actingAs($this->admin)->from('/admin/attributes')->post('/admin/attributes', ['name' => 'sIZE'])
            ->assertRedirect('/admin/attributes')
            ->assertSessionHasErrors('name');
        $this->assertSame(1, Attribute::whereRaw('lower(name) = ?', ['size'])->count());

        $size = $this->attribute('Size');
        $this->actingAs($this->admin)->put("/admin/attributes/{$size->id}", ['name' => 'LENGTH'])->assertSessionHasErrors('name');
        $this->actingAs($this->admin)->put("/admin/attributes/{$size->id}", ['name' => 'SIZE'])->assertSessionHasNoErrors();
        $this->assertSame('SIZE', $size->fresh()->name);
    }

    public function test_values_are_added_renamed_and_deleted_on_the_edit_page(): void
    {
        $size = $this->attribute('Size');

        $this->actingAs($this->admin)->get("/admin/attributes/{$size->id}/edit")
            ->assertOk()->assertSeeInOrder(['Large', 'Medium', 'Small'])->assertSee('Add a value');

        $this->actingAs($this->admin)->post("/admin/attributes/{$size->id}/values", ['value' => 'X-Large'])
            ->assertRedirect("/admin/attributes/{$size->id}/edit")->assertSessionHasNoErrors();
        $this->assertSame(4, $size->values()->count());

        $small = $this->value('Size', 'Small');
        $this->actingAs($this->admin)->put("/admin/attribute-values/{$small->id}", ['value' => 'Tiny'])
            ->assertRedirect("/admin/attributes/{$size->id}/edit");
        $this->assertSame('Tiny', $small->fresh()->value);

        $this->actingAs($this->admin)->delete("/admin/attribute-values/{$small->id}")
            ->assertRedirect("/admin/attributes/{$size->id}/edit");
        $this->assertNull($small->fresh());
        $this->assertSame(3, $size->values()->count());

        $this->actingAs($this->admin)->put('/admin/attribute-values/999999', ['value' => 'x'])->assertNotFound();
    }

    public function test_values_are_unique_within_their_attribute_only(): void
    {
        $size = $this->attribute('Size');
        $length = $this->attribute('Length');

        $this->actingAs($this->admin)->post("/admin/attributes/{$size->id}/values", ['value' => 'large'])
            ->assertSessionHasErrors('value');
        $this->assertSame(3, $size->values()->count());

        // The same text under another attribute is a different choice.
        $this->actingAs($this->admin)->post("/admin/attributes/{$length->id}/values", ['value' => 'Large'])
            ->assertSessionHasNoErrors();
        $this->assertSame(4, $length->values()->count());

        // A rename collision is reported on that row's own error bag.
        $small = $this->value('Size', 'Small');
        $this->actingAs($this->admin)->put("/admin/attribute-values/{$small->id}", ['value' => 'MEDIUM'])
            ->assertSessionHasErrors('value', null, 'value'.$small->id);
        $this->assertSame('Small', $small->fresh()->value);

        // Changing only the case of itself is not a collision.
        $this->actingAs($this->admin)->put("/admin/attribute-values/{$small->id}", ['value' => 'SMALL'])->assertSessionHasNoErrors();
    }

    public function test_an_attribute_offered_by_products_cannot_be_deleted(): void
    {
        $size = $this->attribute('Size');
        [$a, $b] = Product::orderBy('id')->take(2)->get();
        $a->forceFill(['choice_attributes' => [$size->id]])->save();
        $b->forceFill(['choice_attributes' => [(string) $size->id]])->save(); // the storefront's older format

        $this->actingAs($this->admin)->from('/admin/attributes')->delete("/admin/attributes/{$size->id}")
            ->assertRedirect('/admin/attributes')
            ->assertSessionHas('error', '2 products offer this attribute for variants; remove it from those products first.');

        $this->assertNotNull($size->fresh());
        $this->assertSame(3, $size->values()->count());
    }

    public function test_an_unused_attribute_is_deleted_with_its_values(): void
    {
        $length = $this->attribute('Length');
        Product::orderBy('id')->firstOrFail()->forceFill(['choice_attributes' => [$this->attribute('Size')->id]])->save();

        $this->actingAs($this->admin)->delete("/admin/attributes/{$length->id}")
            ->assertRedirect('/admin/attributes')->assertSessionHas('status');

        $this->assertNull($length->fresh());
        $this->assertSame(0, AttributeValue::where('attribute_id', $length->id)->count());
        $this->assertSame(6, AttributeValue::count());
    }
}
