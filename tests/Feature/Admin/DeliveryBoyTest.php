<?php

namespace Tests\Feature\Admin;

use App\Models\DeliveryBoyCollection;
use App\Models\DeliveryBoyPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryBoyTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@wowmart.test')->firstOrFail();
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Kamal Mia',
            'email' => 'kamal@wowmart.test',
            'phone' => '+8801700000201',
            'password' => 'password123',
        ], $overrides);
    }

    public function test_the_seeded_boys_list_and_the_add_form_open(): void
    {
        $this->actingAs($this->admin)->get('/admin/delivery-boys')
            ->assertOk()
            ->assertSee('Ridwan Hassan')
            ->assertSee('Jamal Uddin')
            ->assertSee('Add a delivery boy')
            ->assertViewHas('boys', fn ($page) => $page->total() === 2);
    }

    public function test_a_boy_is_created_edited_and_the_password_hasht(): void
    {
        $this->actingAs($this->admin)->post('/admin/delivery-boys', $this->payload())
            ->assertRedirect('/admin/delivery-boys')->assertSessionHasNoErrors();

        $boy = User::where('email', 'kamal@wowmart.test')->firstOrFail();
        $this->assertSame('delivery_boy', $boy->user_type->value);
        $this->assertNotSame('password123', $boy->password);

        $this->actingAs($this->admin)->get("/admin/delivery-boys/{$boy->id}/edit")
            ->assertOk()->assertSee('value="Kamal Mia"', false);

        $this->actingAs($this->admin)->put("/admin/delivery-boys/{$boy->id}", $this->payload([
            'name' => 'Kamal U.', 'password' => '',
        ]))->assertSessionHasNoErrors();
        $this->assertSame('Kamal U.', $boy->fresh()->name);
    }

    public function test_an_untaken_email_phone_and_a_real_password_are_required(): void
    {
        $this->actingAs($this->admin)->post('/admin/delivery-boys', $this->payload(['email' => 'admin@wowmart.test']))
            ->assertSessionHasErrors('email');

        $this->actingAs($this->admin)->post('/admin/delivery-boys', $this->payload(['phone' => '+8801700000101']))
            ->assertSessionHasErrors('phone');

        $this->actingAs($this->admin)->post('/admin/delivery-boys', $this->payload(['password' => 'short']))
            ->assertSessionHasErrors('password');

        $this->assertSame(2, User::where('user_type', 'delivery_boy')->count());
    }

    public function test_payments_and_collections_are_recorded_and_listed(): void
    {
        $ridwan = User::where('email', 'ridwan@wowmart.test')->firstOrFail();

        $this->actingAs($this->admin)->post('/admin/delivery-boys-payment-histories', [
            'delivery_boy_id' => $ridwan->id, 'amount' => '300', 'note' => 'Weekly payout',
        ])->assertRedirect('/admin/delivery-boys-payment-histories')->assertSessionHasNoErrors();

        $this->actingAs($this->admin)->post('/admin/delivery-boys-collection-histories', [
            'delivery_boy_id' => $ridwan->id, 'amount' => '45.50',
        ])->assertRedirect('/admin/delivery-boys-collection-histories')->assertSessionHasNoErrors();

        $this->assertSame(2, DeliveryBoyPayment::count());
        $this->assertSame(2, DeliveryBoyCollection::count());

        $this->actingAs($this->admin)->get('/admin/delivery-boys-payment-histories')
            ->assertOk()->assertSee('Weekly payout')->assertSee('Ridwan Hassan');
        $this->actingAs($this->admin)->get('/admin/delivery-boys-collection-histories')
            ->assertOk()->assertSee('45.50');
    }

    public function test_a_history_row_requires_a_real_delivery_boy(): void
    {
        // A customer is not a delivery boy.
        $customer = User::factory()->create(['user_type' => 'customer']);

        $this->actingAs($this->admin)->post('/admin/delivery-boys-payment-histories', [
            'delivery_boy_id' => $customer->id, 'amount' => '10',
        ])->assertSessionHasErrors('delivery_boy_id');

        $this->assertSame(1, DeliveryBoyPayment::count());
    }

    public function test_a_boy_with_a_money_trail_cannot_be_deleted(): void
    {
        $ridwan = User::where('email', 'ridwan@wowmart.test')->firstOrFail();

        $this->actingAs($this->admin)->delete("/admin/delivery-boys/{$ridwan->id}")
            ->assertRedirect()
            ->assertSessionHas('error', 'Ridwan Hassan has 1 payment on record; the history is part of the money trail, so remove the boy only after archiving it.');
        $this->assertNotNull($ridwan->fresh());
    }
}
