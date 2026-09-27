<?php

namespace Tests\Feature\Api\V1;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PageApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Sanctum::actingAs(User::where('email', 'admin@wowmart.test')->firstOrFail());
    }

    public function test_a_page_is_created_read_replaced_and_deleted(): void
    {
        $id = $this->postJson('/api/v1/pages', [
            'type' => 'custom', 'title' => 'Returns', 'slug' => 'returns', 'status' => true,
        ])->assertCreated()
            ->assertJsonPath('data.title', 'Returns')
            ->assertJsonPath('data.slug', 'returns')
            ->json('data.id');

        $this->getJson("/api/v1/pages/{$id}")->assertOk()->assertJsonPath('data.title', 'Returns');

        $this->putJson("/api/v1/pages/{$id}", [
            'type' => 'custom', 'title' => 'Returns & Exchanges', 'slug' => 'returns', 'status' => false,
        ])->assertOk()->assertJsonPath('data.title', 'Returns & Exchanges')->assertJsonPath('data.status', false);

        $this->patchJson("/api/v1/pages/{$id}", ['title' => 'x'])->assertMethodNotAllowed();

        $this->deleteJson("/api/v1/pages/{$id}")->assertNoContent();
        $this->assertSame(3, Page::count()); // the seeded three remain
    }

    public function test_a_bad_type_is_422(): void
    {
        $this->postJson('/api/v1/pages', ['type' => 'nonsense', 'title' => 'X', 'slug' => 'x'])
            ->assertUnprocessable()->assertJsonValidationErrors('type');
    }
}
