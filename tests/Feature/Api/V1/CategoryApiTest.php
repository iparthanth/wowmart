<?php

namespace Tests\Feature\Api\V1;

use App\Models\Category;
use App\Models\Upload;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CategoryApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Sanctum::actingAs(User::where('email', 'admin@wowmart.test')->firstOrFail());
    }

    public function test_list_is_paginated_searchable_and_counts_products(): void
    {
        $this->getJson('/api/v1/categories?per_page=5')
            ->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.total', Category::count())
            ->assertJsonStructure(['data' => [['id', 'name', 'slug', 'parent_id', 'level', 'order_level', 'featured', 'digital', 'icon', 'banner', 'cover_image', 'meta_title', 'meta_description', 'product_count']], 'links', 'meta']);

        $c = Category::whereHas('products')->firstOrFail();
        $this->getJson('/api/v1/categories?search='.urlencode($c->name))
            ->assertOk()
            ->assertJsonPath('data.0.id', $c->id)
            ->assertJsonPath('data.0.product_count', $c->products()->count());

        $this->getJson('/api/v1/categories?search=%25')->assertOk()->assertJsonPath('meta.total', 0);
        $this->getJson('/api/v1/categories?search[]=x')->assertUnprocessable()->assertJsonValidationErrors('search');
    }

    public function test_form_style_string_input_serialises_with_the_same_types_as_a_read(): void
    {
        $parent = Category::firstOrFail();

        $created = $this->postJson('/api/v1/categories', ['name' => 'Probe', 'parent_id' => (string) $parent->id, 'order_level' => '7', 'featured' => '1'])
            ->assertCreated()
            ->json('data');

        $this->assertSame([$parent->id, 7, true], [$created['parent_id'], $created['order_level'], $created['featured']]);
        $this->assertSame($created, $this->getJson("/api/v1/categories/{$created['id']}")->json('data'));
    }

    public function test_junk_flag_values_are_422_not_false(): void
    {
        $this->postJson('/api/v1/categories', ['name' => 'Probe', 'featured' => 'banana', 'digital' => ['x']])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['featured', 'digital']);
    }

    public function test_crud_round_trip(): void
    {
        $icon = Upload::where('type', 'image')->firstOrFail();

        $id = $this->postJson('/api/v1/categories', ['name' => 'Drones', 'icon' => $icon->id, 'featured' => true])
            ->assertCreated()
            ->assertJsonPath('data.slug', 'drones')
            ->assertJsonPath('data.level', 0)
            ->assertJsonPath('data.featured', true)
            ->assertJsonPath('data.digital', false)
            ->assertJsonPath('data.icon.id', $icon->id)
            ->assertJsonPath('data.banner', null)
            ->json('data.id');

        $parent = Category::where('level', 0)->where('id', '!=', $id)->firstOrFail();

        // PUT is a full replacement: featured and icon are dropped because they are not sent.
        $this->putJson("/api/v1/categories/{$id}", ['name' => 'Racing drones', 'parent_id' => $parent->id, 'order_level' => 7])
            ->assertOk()
            ->assertJsonPath('data.name', 'Racing drones')
            ->assertJsonPath('data.slug', 'drones')
            ->assertJsonPath('data.level', 1)
            ->assertJsonPath('data.order_level', 7)
            ->assertJsonPath('data.featured', false)
            ->assertJsonPath('data.icon', null);

        $this->getJson("/api/v1/categories/{$id}")->assertOk()->assertJsonPath('data.parent_id', $parent->id);

        $this->deleteJson("/api/v1/categories/{$id}")->assertNoContent();
        $this->getJson("/api/v1/categories/{$id}")->assertNotFound();
    }

    public function test_flags_set_exactly_the_given_value(): void
    {
        $c = Category::where('featured', false)->firstOrFail();

        foreach ([true, true, false] as $value) {
            $this->patchJson("/api/v1/categories/{$c->id}/flags", ['featured' => $value])
                ->assertOk()
                ->assertJsonPath('data.featured', $value);
        }

        $this->patchJson("/api/v1/categories/{$c->id}/flags", [])->assertUnprocessable()->assertJsonValidationErrors('featured');
        $this->patchJson("/api/v1/categories/{$c->id}/flags", ['featured' => 'maybe'])->assertUnprocessable();
    }

    public function test_business_rule_refusals_are_409(): void
    {
        $used = Category::whereHas('products')->firstOrFail();

        $this->deleteJson("/api/v1/categories/{$used->id}")
            ->assertStatus(409)
            ->assertJsonPath('message', fn (string $m) => str_contains($m, 'Move') && str_contains($m, 'first'));

        $this->assertModelExists($used);
    }

    public function test_validation_is_422_including_cycles(): void
    {
        $this->postJson('/api/v1/categories', ['name' => str_repeat('x', 101)])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('name');

        $top = $this->postJson('/api/v1/categories', ['name' => 'Top'])->json('data.id');
        $sub = $this->postJson('/api/v1/categories', ['name' => 'Sub', 'parent_id' => $top])->json('data.id');

        $this->putJson("/api/v1/categories/{$top}", ['name' => 'Top', 'parent_id' => $sub])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('parent_id');
    }
}
