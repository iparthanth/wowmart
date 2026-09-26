<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DeliveryBoyApiTest extends TestCase
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
            'name' => 'Kamal Mia',
            'email' => 'kamal@wowmart.test',
            'phone' => '+8801700000201',
            'password' => 'password123',
        ], $overrides);
    }

    public function test_the_seeded_boys_list_with_their_contact_details(): void
    {
        $response = $this->getJson('/api/v1/delivery-boys')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.name', 'Jamal Uddin')
            ->assertJsonPath('data.0.phone', '+8801700000102');

        $this->assertArrayNotHasKey('password', $response->json('data.0'));
    }

    public function test_a_boy_is_created_read_replaced_and_deleted(): void
    {
        $id = $this->postJson('/api/v1/delivery-boys', $this->payload())
            ->assertCreated()
            ->assertJsonPath('data.name', 'Kamal Mia')
            ->json('data.id');

        $this->assertNotSame('password123', User::find($id)->password);

        $this->getJson("/api/v1/delivery-boys/{$id}")->assertOk()->assertJsonPath('data.name', 'Kamal Mia');

        $this->putJson("/api/v1/delivery-boys/{$id}", $this->payload(['name' => 'Kamal U.']))
            ->assertOk()->assertJsonPath('data.name', 'Kamal U.');

        $this->patchJson("/api/v1/delivery-boys/{$id}", ['name' => 'x'])->assertMethodNotAllowed();

        $this->deleteJson("/api/v1/delivery-boys/{$id}")->assertNoContent();
        $this->assertNull(User::find($id));
    }

    public function test_payments_and_collections_round_trip(): void
    {
        $ridwan = User::where('email', 'ridwan@wowmart.test')->firstOrFail();

        $paymentResponse = $this->postJson('/api/v1/delivery-boys-payment-histories', [
            'delivery_boy_id' => $ridwan->id, 'amount' => '300', 'note' => 'Weekly payout',
        ])->assertCreated()->assertJsonPath('data.kind', 'payment');

        $this->assertEquals(300.0, $paymentResponse->json('data.amount'));
        $payment = $paymentResponse->json('data.id');

        $collection = $this->postJson('/api/v1/delivery-boys-collection-histories', [
            'delivery_boy_id' => $ridwan->id, 'amount' => '45.5',
        ])->assertCreated()->assertJsonPath('data.kind', 'collection')->json('data.id');

        $this->getJson('/api/v1/delivery-boys-payment-histories')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.delivery_boy.name', 'Ridwan Hassan');

        $this->getJson('/api/v1/delivery-boys-collection-histories')
            ->assertOk()
            ->assertJsonPath('data.0.id', $collection);

        // A money trail is append-only: there is no update route at all.
        $this->putJson("/api/v1/delivery-boys-payment-histories/{$payment}", ['amount' => '1'])
            ->assertNotFound();
    }

    public function test_a_boy_with_a_money_trail_is_409_on_delete(): void
    {
        $ridwan = User::where('email', 'ridwan@wowmart.test')->firstOrFail();

        $this->deleteJson("/api/v1/delivery-boys/{$ridwan->id}")
            ->assertConflict()
            ->assertJsonPath('message', 'Ridwan Hassan has 1 payment on record; the history is part of the money trail, so remove the boy only after archiving it.');
    }
}
