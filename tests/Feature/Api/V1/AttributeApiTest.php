<?php

namespace Tests\Feature\Api\V1;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AttributeApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Sanctum::actingAs(User::where('email', 'admin@wowmart.test')->firstOrFail());
    }

    private function attribute(string $name): Attribute
    {
        return Attribute::where('name', $name)->firstOrFail();
    }

    public function test_the_list_is_paginated_searchable_and_counts_values(): void
    {
        $this->getJson('/api/v1/attributes?per_page=2')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 3)
            ->assertJsonPath('data.0.name', 'Capacity')
            ->assertJsonPath('data.0.value_count', 3)
            ->assertJsonMissingPath('data.0.values');

        $this->getJson('/api/v1/attributes?search=len')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.name', 'Length');
        $this->getJson('/api/v1/attributes?search[]=x')->assertOk()->assertJsonPath('meta.total', 3);
    }

    public function test_an_attribute_is_created_shown_and_replaced(): void
    {
        $id = $this->postJson('/api/v1/attributes', ['name' => 'Voltage'])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Voltage')
            ->assertJsonPath('data.value_count', 0)
            ->assertJsonPath('data.values', [])
            ->json('data.id');

        $this->putJson("/api/v1/attributes/{$id}", ['name' => 'Input voltage'])->assertOk()->assertJsonPath('data.name', 'Input voltage');
        $this->patchJson("/api/v1/attributes/{$id}", ['name' => 'x'])->assertMethodNotAllowed();

        $size = $this->attribute('Size');
        $this->getJson("/api/v1/attributes/{$size->id}")
            ->assertOk()
            ->assertJsonPath('data.values.*.value', ['Large', 'Medium', 'Small'])
            ->assertJsonPath('data.values.0.attribute_id', $size->id);
    }

    public function test_names_are_unique_in_any_case(): void
    {
        $this->postJson('/api/v1/attributes', ['name' => 'capacity'])->assertUnprocessable()->assertJsonValidationErrors('name');
        $this->postJson('/api/v1/attributes', ['name' => ['Size']])->assertUnprocessable()->assertJsonValidationErrors('name');
    }

    public function test_values_crud_under_an_attribute(): void
    {
        $size = $this->attribute('Size');

        $this->getJson("/api/v1/attributes/{$size->id}/values")
            ->assertOk()->assertJsonPath('meta.total', 3)->assertJsonPath('data.*.value', ['Large', 'Medium', 'Small']);

        $id = $this->postJson("/api/v1/attributes/{$size->id}/values", ['value' => 'X-Large'])
            ->assertCreated()
            ->assertJsonPath('data.attribute_id', $size->id)
            ->assertJsonPath('data.value', 'X-Large')
            ->json('data.id');

        $this->postJson("/api/v1/attributes/{$size->id}/values", ['value' => 'x-large'])
            ->assertUnprocessable()->assertJsonValidationErrors('value');

        $this->putJson("/api/v1/attribute-values/{$id}", ['value' => 'XL'])->assertOk()->assertJsonPath('data.value', 'XL');
        $this->putJson("/api/v1/attribute-values/{$id}", ['value' => 'Small'])->assertUnprocessable()->assertJsonValidationErrors('value');
        $this->patchJson("/api/v1/attribute-values/{$id}", ['value' => 'x'])->assertMethodNotAllowed();

        $this->deleteJson("/api/v1/attribute-values/{$id}")->assertNoContent();
        $this->assertNull(AttributeValue::find($id));
        $this->deleteJson("/api/v1/attribute-values/{$id}")->assertNotFound();
        $this->getJson('/api/v1/attributes/999999/values')->assertNotFound();
    }

    public function test_a_value_may_repeat_under_another_attribute(): void
    {
        $length = $this->attribute('Length');

        $this->postJson("/api/v1/attributes/{$length->id}/values", ['value' => 'SMALL'])->assertCreated();
        $this->assertSame(4, $length->values()->count());
    }

    public function test_an_attribute_offered_by_a_product_is_409(): void
    {
        $size = $this->attribute('Size');
        Product::orderBy('id')->firstOrFail()->forceFill(['choice_attributes' => [$size->id]])->save();

        $this->deleteJson("/api/v1/attributes/{$size->id}")
            ->assertConflict()
            ->assertJsonPath('message', '1 product offers this attribute for variants; remove it from that product first.');

        $capacity = $this->attribute('Capacity');
        $this->deleteJson("/api/v1/attributes/{$capacity->id}")->assertNoContent();
        $this->assertNull($capacity->fresh());
        $this->assertSame(6, AttributeValue::count());
    }
}
