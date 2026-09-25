<?php

namespace Tests\Feature\Api\V1;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SellerApiTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@wowmart.test')->firstOrFail();
        Sanctum::actingAs($this->admin);
    }

    private function seller(string $shop): User
    {
        return Shop::where('name', $shop)->firstOrFail()->user;
    }

    public function test_the_list_is_paginated_and_filtered(): void
    {
        $this->getJson('/api/v1/sellers?per_page=2')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 5)
            ->assertJsonStructure(['data' => [[
                'id', 'name', 'email', 'phone', 'banned', 'product_count', 'order_line_count', 'created_at',
                'shop' => ['id', 'name', 'slug', 'logo', 'address', 'phone', 'rating', 'num_of_reviews', 'verification_status'],
            ]], 'links', 'meta']);

        $this->getJson('/api/v1/sellers?verification=pending')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.shop.name', 'Volt Street')
            ->assertJsonPath('data.0.shop.verification_status', 'pending');

        $this->getJson('/api/v1/sellers?search=circuit')
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.product_count', 2)
            ->assertJsonPath('data.0.order_line_count', 1)
            ->assertJsonPath('data.0.banned', false)
            ->assertJsonPath('data.0.shop.rating', '0.00');

        $this->getJson('/api/v1/sellers?verification[]=x&status=nope')->assertOk()->assertJsonPath('meta.total', 5);
    }

    public function test_show_resolves_sellers_only(): void
    {
        $seller = $this->seller('Maker Den');

        $this->getJson("/api/v1/sellers/{$seller->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $seller->id)
            ->assertJsonPath('data.shop.name', 'Maker Den')
            ->assertJsonPath('data.product_count', 2)
            ->assertJsonPath('data.order_line_count', 0);

        $customer = User::where('email', 'rafi@customers.wowmart.test')->firstOrFail();

        foreach ([$customer, $this->admin] as $user) {
            $this->getJson("/api/v1/sellers/{$user->id}")->assertNotFound();
            $this->patchJson("/api/v1/sellers/{$user->id}/flags", ['banned' => true])->assertNotFound();
            $this->deleteJson("/api/v1/sellers/{$user->id}")->assertNotFound();
        }

        $this->assertFalse($this->admin->fresh()->banned);
    }

    public function test_verification_is_set_and_repeating_it_is_harmless(): void
    {
        $volt = $this->seller('Volt Street');

        $this->patchJson("/api/v1/sellers/{$volt->id}/verification", ['status' => 'approved'])
            ->assertOk()->assertJsonPath('data.shop.verification_status', 'approved');
        $this->patchJson("/api/v1/sellers/{$volt->id}/verification", ['status' => 'approved'])
            ->assertOk()->assertJsonPath('data.shop.verification_status', 'approved');
        $this->patchJson("/api/v1/sellers/{$volt->id}/verification", ['status' => 'rejected'])
            ->assertOk()->assertJsonPath('data.shop.verification_status', 'rejected');

        $this->patchJson("/api/v1/sellers/{$volt->id}/verification", ['status' => 'pending'])
            ->assertUnprocessable()->assertJsonValidationErrors('status');
        $this->assertSame('rejected', $volt->shop->fresh()->verification_status);
    }

    public function test_the_banned_flag_is_set_exactly_and_junk_is_422(): void
    {
        $seller = $this->seller('Sensor Bazaar');

        foreach ([true, true] as $value) {
            $this->patchJson("/api/v1/sellers/{$seller->id}/flags", ['banned' => $value])
                ->assertOk()->assertJsonPath('data.banned', true);
        }
        $this->patchJson("/api/v1/sellers/{$seller->id}/flags", ['banned' => false])->assertJsonPath('data.banned', false);

        $this->patchJson("/api/v1/sellers/{$seller->id}/flags", ['banned' => 'maybe'])
            ->assertUnprocessable()->assertJsonValidationErrors('banned');
        $this->patchJson("/api/v1/sellers/{$seller->id}/flags", [])
            ->assertUnprocessable()->assertJsonValidationErrors('banned');
        $this->assertFalse($seller->fresh()->banned);
    }

    public function test_delete_is_409_while_the_seller_has_sales_and_204_when_empty(): void
    {
        $seller = $this->seller('Receipt Roll Co');

        $this->deleteJson("/api/v1/sellers/{$seller->id}")
            ->assertStatus(409)
            ->assertJsonPath('message', 'Receipt Roll Co has 2 products and 1 order line; ban the seller instead.');
        $this->assertNotNull($seller->fresh());

        $empty = User::factory()->seller()->create(['name' => 'Fresh Stall']);
        $shop = new Shop(['user_id' => $empty->id, 'name' => 'Fresh Stall']);
        $shop->forceFill(['slug' => 'fresh-stall'])->save();

        $this->deleteJson("/api/v1/sellers/{$empty->id}")->assertNoContent();
        $this->assertNull($empty->fresh());
        $this->assertNull($shop->fresh());
    }
}
