<?php

namespace Tests\Feature\Api\V1;

use App\Enums\UserType;
use App\Models\Address;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CustomerApiTest extends TestCase
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

    private function nusrat(): User
    {
        return User::where('email', 'nusrat@customers.wowmart.test')->firstOrFail();
    }

    public function test_the_list_is_filtered_paginated_and_never_shows_passwords(): void
    {
        User::factory()->banned()->create(['name' => 'Banned Bashir']); // verified by the factory

        $response = $this->getJson('/api/v1/customers?per_page=2')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 7)
            ->assertJsonStructure(['data' => [['id', 'name', 'email', 'country_code', 'phone', 'email_verified', 'email_verified_at', 'banned', 'joined_at', 'order_count', 'total_spent']], 'links', 'meta']);
        $this->assertStringNotContainsString('password', $response->getContent());
        $this->assertStringNotContainsString('remember_token', $response->getContent());

        $this->getJson('/api/v1/customers?status=banned')->assertJsonPath('meta.total', 1)->assertJsonPath('data.0.banned', true);
        $this->getJson('/api/v1/customers?verification=verified')->assertJsonPath('meta.total', 1);
        $this->getJson('/api/v1/customers?verification=unverified')->assertJsonPath('meta.total', 6);
        $this->getJson('/api/v1/customers?search=nusrat')
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.order_count', 5)
            ->assertJsonPath('data.0.total_spent', '1402.50'); // her cancelled order is left out
        $this->getJson('/api/v1/customers?sort=spent')->assertJsonPath('data.0.name', 'Tanvir Hasan');
    }

    public function test_show_includes_addresses_and_recent_orders(): void
    {
        $nusrat = $this->nusrat();
        Address::create(['user_id' => $nusrat->id, 'name' => 'Nusrat', 'phone' => '01700000000', 'address' => 'Mirpur 10', 'city' => 'Dhaka', 'set_default' => true]);
        $latest = Order::where('user_id', $nusrat->id)->latest('id')->firstOrFail();

        $this->getJson("/api/v1/customers/{$nusrat->id}")
            ->assertOk()
            ->assertJsonPath('data.addresses.0.city', 'Dhaka')
            ->assertJsonPath('data.addresses.0.default', true)
            ->assertJsonCount(5, 'data.recent_orders')
            ->assertJsonPath('data.recent_orders.0.code', $latest->code)
            ->assertJsonPath('data.order_count', 5)
            ->assertJsonPath('data.email_verified', false);
    }

    public function test_admin_and_seller_ids_are_404(): void
    {
        $seller = User::where('user_type', UserType::Seller)->firstOrFail();

        foreach ([$this->admin->id, $seller->id, 999999] as $id) {
            $this->getJson("/api/v1/customers/{$id}")->assertNotFound();
            $this->patchJson("/api/v1/customers/{$id}/flags", ['banned' => true])->assertNotFound();
            $this->deleteJson("/api/v1/customers/{$id}")->assertNotFound();
        }

        $this->assertFalse($this->admin->fresh()->banned);
    }

    public function test_create_replace_and_the_write_only_password(): void
    {
        $id = $this->postJson('/api/v1/customers', [
            'name' => 'Farhana Akter', 'email' => 'farhana@example.com', 'phone' => '01811 222333',
            'password' => 'secret-pass', 'password_confirmation' => 'secret-pass', 'user_type' => 'admin',
        ])
            ->assertCreated()
            ->assertJsonPath('data.phone', '1811222333')
            ->assertJsonPath('data.country_code', '+880')
            ->assertJsonPath('data.banned', false)
            ->assertJsonPath('data.email_verified', false)
            ->assertJsonPath('data.order_count', 0)
            ->assertJsonPath('data.total_spent', '0.00')
            ->assertJsonMissingPath('data.password')
            ->json('data.id');

        $this->assertSame(UserType::Customer, User::findOrFail($id)->user_type);

        $this->postJson('/api/v1/customers', ['name' => 'Nobody', 'password' => 'secret-pass', 'password_confirmation' => 'secret-pass'])
            ->assertUnprocessable()->assertJsonValidationErrors('email');
        $this->postJson('/api/v1/customers', ['name' => 'Dup', 'email' => 'FARHANA@example.COM', 'password' => 'secret-pass', 'password_confirmation' => 'secret-pass'])
            ->assertUnprocessable()->assertJsonValidationErrors('email');

        // PUT without a password keeps it; PUT is a full replacement otherwise.
        $this->putJson("/api/v1/customers/{$id}", ['name' => 'Farhana A.', 'email' => 'farhana@example.com'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Farhana A.')
            ->assertJsonPath('data.phone', null)
            ->assertJsonPath('data.country_code', null);
        $this->assertTrue(Hash::check('secret-pass', User::findOrFail($id)->password));

        $this->patchJson("/api/v1/customers/{$id}", ['name' => 'Partial'])->assertMethodNotAllowed();
    }

    public function test_flags_are_idempotent_and_validated(): void
    {
        $nusrat = $this->nusrat();

        $this->patchJson("/api/v1/customers/{$nusrat->id}/flags", ['banned' => true])->assertOk()->assertJsonPath('data.banned', true);
        $this->patchJson("/api/v1/customers/{$nusrat->id}/flags", ['banned' => true])->assertOk()->assertJsonPath('data.banned', true);
        $this->patchJson("/api/v1/customers/{$nusrat->id}/flags", ['banned' => 'yes please'])->assertUnprocessable();
        $this->patchJson("/api/v1/customers/{$nusrat->id}/flags", ['banned' => false])->assertOk()->assertJsonPath('data.banned', false);
    }

    public function test_delete_is_409_with_orders_and_204_without(): void
    {
        $nusrat = $this->nusrat();

        $this->deleteJson("/api/v1/customers/{$nusrat->id}")
            ->assertStatus(409)
            ->assertJsonPath('message', 'Nusrat Jahan has 5 orders; order history must stay. Ban the account instead.');

        $fresh = User::factory()->create();
        $this->deleteJson("/api/v1/customers/{$fresh->id}")->assertNoContent();
        $this->assertDatabaseMissing('users', ['id' => $fresh->id]);
    }
}
