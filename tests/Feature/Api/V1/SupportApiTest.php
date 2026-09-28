<?php

namespace Tests\Feature\Api\V1;

use App\Models\ContactMessage;
use App\Models\ProductConversation;
use App\Models\ProductQuery;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SupportApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Sanctum::actingAs(User::where('email', 'admin@wowmart.test')->firstOrFail());
    }

    public function test_tickets_round_trip_through_the_api(): void
    {
        $this->getJson('/api/v1/tickets')->assertOk()->assertJsonCount(5, 'data');

        $customer = User::where('email', 'rafi@customers.wowmart.test')->firstOrFail();
        $id = $this->postJson('/api/v1/tickets', [
            'user_id' => $customer->id,
            'subject' => 'API opened ticket',
            'message' => 'Opened through the API.',
        ])->assertCreated()
            ->assertJsonPath('data.subject', 'API opened ticket')
            ->json('data.id');

        $this->getJson("/api/v1/tickets/{$id}")->assertOk()->assertJsonPath('data.id', $id);

        $this->postJson("/api/v1/tickets/{$id}/reply", ['message' => 'Staff reply through the API.'])
            ->assertOk();
        $this->assertDatabaseCount('support_ticket_replies', 10); // 8 seeded + 1 store opener + 1 reply

        $this->postJson("/api/v1/tickets/{$id}/status", ['status' => 'closed'])
            ->assertOk()->assertJsonPath('data.status', 'closed');

        $this->putJson("/api/v1/tickets/{$id}", ['subject' => 'Renamed by PUT'])
            ->assertOk()->assertJsonPath('data.subject', 'Renamed by PUT');

        $this->patchJson("/api/v1/tickets/{$id}", ['subject' => 'x'])->assertMethodNotAllowed();

        $this->deleteJson("/api/v1/tickets/{$id}")->assertNoContent();
    }

    public function test_contact_messages_round_trip_and_replies_mark_them_answered(): void
    {
        $this->getJson('/api/v1/contacts')->assertOk()->assertJsonCount(4, 'data');

        $id = $this->postJson('/api/v1/contacts', [
            'name' => 'Api Visitor',
            'email' => 'visitor@example.test',
            'query' => 'Do you deliver to Khulna?',
        ])->assertCreated()->assertJsonPath('data.status', 'open')->json('data.id');

        $this->putJson("/api/v1/contacts/{$id}", [
            'name' => 'Api Visitor',
            'email' => 'visitor@example.test',
            'query' => 'Do you deliver to Khulna?',
            'reply' => 'Yes, delivery covers Khulna city.',
        ])->assertOk()->assertJsonPath('data.status', 'answered');

        $this->deleteJson("/api/v1/contacts/{$id}")->assertNoContent();
        $this->assertSame(0, ContactMessage::where('email', 'visitor@example.test')->count());
    }

    public function test_product_queries_are_answered_with_put(): void
    {
        $query = ProductQuery::where('status', 'open')->firstOrFail();

        $this->putJson("/api/v1/product-queries/{$query->id}", [
            'reply' => 'Gift wrapping ships in original packaging.',
        ])->assertOk()->assertJsonPath('data.status', 'answered');

        $this->deleteJson("/api/v1/product-queries/{$query->id}")->assertNoContent();
        $this->assertModelMissing($query);
    }

    public function test_conversations_round_trip_and_reply(): void
    {
        $this->getJson('/api/v1/conversations')->assertOk()->assertJsonCount(2, 'data');

        $conversation = ProductConversation::firstOrFail();
        $id = $conversation->id;

        $this->getJson("/api/v1/conversations/{$id}")->assertOk()->assertJsonPath('data.id', $id);

        $this->postJson("/api/v1/conversations/{$id}/reply", ['message' => 'API message from staff.'])
            ->assertOk();

        $this->putJson("/api/v1/conversations/{$id}", ['status' => 'closed'])
            ->assertOk()->assertJsonPath('data.status', 'closed');

        $this->deleteJson("/api/v1/conversations/{$id}")->assertNoContent();
        $this->assertModelMissing($conversation);
    }

    public function test_the_support_api_needs_an_admin(): void
    {
        Sanctum::actingAs(User::where('email', 'rafi@customers.wowmart.test')->firstOrFail());

        $this->postJson('/api/v1/tickets', ['user_id' => 1, 'subject' => 'x', 'message' => 'y'])
            ->assertForbidden();
        $this->getJson('/api/v1/contacts')->assertForbidden();
    }
}
