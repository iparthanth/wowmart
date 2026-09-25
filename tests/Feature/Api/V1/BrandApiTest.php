<?php

namespace Tests\Feature\Api\V1;

use App\Models\Brand;
use App\Models\Upload;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BrandApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Sanctum::actingAs(User::where('email', 'admin@wowmart.test')->firstOrFail());
    }

    public function test_the_list_is_paginated_and_searchable(): void
    {
        $this->getJson('/api/v1/brands?per_page=2')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 4)
            ->assertJsonStructure(['data' => [['id', 'name', 'slug', 'top', 'logo', 'meta_title', 'meta_description', 'product_count']], 'links', 'meta']);

        $this->getJson('/api/v1/brands?search=rasp')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Raspberry Pi');

        $this->getJson('/api/v1/brands?search[]=x')->assertOk()->assertJsonPath('meta.total', 4);
    }

    public function test_an_invalid_top_is_422_not_silently_false(): void
    {
        $brand = Brand::where('name', 'Kingston')->firstOrFail();

        $this->putJson("/api/v1/brands/{$brand->id}", ['name' => 'Kingston', 'top' => 'maybe'])
            ->assertUnprocessable()->assertJsonValidationErrors('top');
        $this->postJson('/api/v1/brands', ['name' => 'Zeta', 'top' => ['x']])
            ->assertUnprocessable()->assertJsonValidationErrors('top');
    }

    public function test_patch_is_not_a_partial_update_that_wipes_fields(): void
    {
        $logo = Upload::where('type', 'image')->firstOrFail();
        $id = $this->postJson('/api/v1/brands', ['name' => 'Adafruit', 'logo' => $logo->id, 'top' => true])->json('data.id');

        $this->patchJson("/api/v1/brands/{$id}", ['name' => 'Adafruit Inc'])->assertMethodNotAllowed();

        $this->getJson("/api/v1/brands/{$id}")
            ->assertJsonPath('data.name', 'Adafruit')
            ->assertJsonPath('data.logo.id', $logo->id)
            ->assertJsonPath('data.top', true);
    }

    public function test_create_show_replace_and_delete(): void
    {
        $logo = Upload::where('type', 'image')->firstOrFail();

        $id = $this->postJson('/api/v1/brands', ['name' => 'Adafruit', 'logo' => $logo->id, 'top' => true])
            ->assertCreated()
            ->assertJsonPath('data.slug', 'adafruit')
            ->assertJsonPath('data.top', true)
            ->assertJsonPath('data.logo.id', $logo->id)
            ->assertJsonPath('data.logo.url', $logo->url())
            ->assertJsonPath('data.product_count', 0)
            ->json('data.id');

        $this->getJson("/api/v1/brands/{$id}")->assertOk()->assertJsonPath('data.name', 'Adafruit');

        // PUT is a full replacement: what is left out is cleared.
        $this->putJson("/api/v1/brands/{$id}", ['name' => 'Adafruit Industries'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Adafruit Industries')
            ->assertJsonPath('data.slug', 'adafruit')
            ->assertJsonPath('data.top', false)
            ->assertJsonPath('data.logo', null);

        $this->deleteJson("/api/v1/brands/{$id}")->assertNoContent();
        $this->assertDatabaseMissing('brands', ['id' => $id]);
    }

    public function test_a_duplicate_name_is_422(): void
    {
        $this->postJson('/api/v1/brands', ['name' => 'lego'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('name');
    }

    public function test_flags_set_exactly_the_given_value(): void
    {
        $brand = Brand::where('name', 'Kingston')->firstOrFail();

        foreach ([true, true, false] as $top) {
            $this->patchJson("/api/v1/brands/{$brand->id}/flags", ['top' => $top])
                ->assertOk()
                ->assertJsonPath('data.top', $top);
        }

        $this->patchJson("/api/v1/brands/{$brand->id}/flags", [])->assertUnprocessable()->assertJsonValidationErrors('top');
        $this->patchJson("/api/v1/brands/{$brand->id}/flags", ['top' => 'maybe'])->assertUnprocessable();
    }

    public function test_deleting_a_brand_in_use_is_409(): void
    {
        $brand = Brand::where('name', 'Arduino')->firstOrFail();

        $this->deleteJson("/api/v1/brands/{$brand->id}")
            ->assertStatus(409)
            ->assertJsonPath('message', fn (string $m) => str_contains($m, 'move them to another brand first'));

        $this->assertModelExists($brand);
    }
}
