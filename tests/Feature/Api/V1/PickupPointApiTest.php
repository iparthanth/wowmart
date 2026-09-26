<?php

namespace Tests\Feature\Api\V1;

use App\Models\PickupPoint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PickupPointApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Sanctum::actingAs(User::where('email', 'admin@wowmart.test')->firstOrFail());
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'GEC Circle branch',
            'phone' => '+8801700000001',
            'address' => ['address' => 'House 12, Road 4, GEC Circle', 'city' => 'Chattogram', 'country' => 'Bangladesh'],
            'status' => true,
        ], $overrides);
    }

    public function test_a_point_is_created_read_replaced_and_deleted(): void
    {
        $id = $this->postJson('/api/v1/pickup-points', $this->payload())
            ->assertCreated()
            ->assertJsonPath('data.name', 'GEC Circle branch')
            ->assertJsonPath('data.address.city', 'Chattogram')
            ->assertJsonPath('data.status', true)
            ->json('data.id');

        $this->getJson("/api/v1/pickup-points/{$id}")->assertOk()->assertJsonPath('data.name', 'GEC Circle branch');

        $this->putJson("/api/v1/pickup-points/{$id}", $this->payload(['name' => 'Agrabad branch', 'status' => false]))
            ->assertOk()->assertJsonPath('data.name', 'Agrabad branch')->assertJsonPath('data.status', false);

        $this->patchJson("/api/v1/pickup-points/{$id}", ['name' => 'x'])->assertMethodNotAllowed();

        $this->deleteJson("/api/v1/pickup-points/{$id}")->assertNoContent();
        $this->assertSame(0, PickupPoint::count());
    }

    public function test_a_bad_payload_is_422(): void
    {
        $this->postJson('/api/v1/pickup-points', $this->payload(['address' => 'plain text']))
            ->assertUnprocessable()->assertJsonValidationErrors('address');

        $this->postJson('/api/v1/pickup-points', $this->payload(['phone' => null]))
            ->assertUnprocessable()->assertJsonValidationErrors('phone');
    }
}
