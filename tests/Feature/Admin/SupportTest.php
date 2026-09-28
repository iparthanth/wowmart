<?php

namespace Tests\Feature\Admin;

use App\Models\ContactMessage;
use App\Models\ProductConversation;
use App\Models\ProductQuery;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@wowmart.test')->firstOrFail();
    }

    public function test_the_four_support_screens_list_seeded_records(): void
    {
        $this->actingAs($this->admin)->get('/admin/support/tickets')
            ->assertOk()
            ->assertSee('My order arrived with a broken part')
            ->assertSee('TKT-');

        $this->actingAs($this->admin)->get('/admin/support/contacts')
            ->assertOk()->assertSee('farhan@example.test');

        $this->actingAs($this->admin)->get('/admin/support/product-queries')
            ->assertOk()->assertSee('Is this kit compatible with Arduino Mega?');

        $this->actingAs($this->admin)->get('/admin/support/conversations')
            ->assertOk()->assertSee('Bulk discount for 20 pieces');
    }

    public function test_a_ticket_is_opened_on_behalf_of_a_customer_and_answered(): void
    {
        $customer = User::where('email', 'rafi@customers.wowmart.test')->firstOrFail();

        $this->actingAs($this->admin)->post('/admin/support/tickets', [
            'user_id' => $customer->id,
            'subject' => 'Replacement for wrong size',
            'message' => 'I ordered size M but received size S.',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $ticket = SupportTicket::where('subject', 'Replacement for wrong size')->firstOrFail();
        $this->assertMatchesRegularExpression('/^TKT-\d{6}$/', $ticket->code);
        $this->assertSame(1, $ticket->replies()->count());

        $this->actingAs($this->admin)->get("/admin/support/tickets/{$ticket->id}")
            ->assertOk()->assertSee('Replacement for wrong size')->assertSee('I ordered size M');

        $this->actingAs($this->admin)->post("/admin/support/tickets/{$ticket->id}/reply", [
            'message' => 'A courier collects the wrong item tomorrow.',
        ])->assertRedirect()->assertSessionHas('status');
        $this->assertSame(2, $ticket->replies()->count());

        $this->actingAs($this->admin)->post("/admin/support/tickets/{$ticket->id}/status", ['status' => 'closed'])
            ->assertRedirect()->assertSessionHas('status');
        $this->assertSame('closed', $ticket->fresh()->status);

        // A closed ticket refuses replies until it is reopened.
        $this->actingAs($this->admin)->post("/admin/support/tickets/{$ticket->id}/reply", ['message' => 'hello?'])
            ->assertRedirect()->assertSessionHas('error');

        $this->actingAs($this->admin)->post("/admin/support/tickets/{$ticket->id}/status", ['status' => 'open'])
            ->assertRedirect()->assertSessionHas('status');
        $this->assertSame('open', $ticket->fresh()->status);
    }

    public function test_contact_messages_are_answered_and_deletable(): void
    {
        $contact = ContactMessage::where('status', 'open')->firstOrFail();

        $this->actingAs($this->admin)->post("/admin/support/contacts/{$contact->id}/reply", [
            'reply' => 'Yes, cash on delivery covers Chattogram.',
        ])->assertRedirect()->assertSessionHas('status');
        $this->assertSame('answered', $contact->fresh()->status);

        $this->actingAs($this->admin)->delete("/admin/support/contacts/{$contact->id}")
            ->assertRedirect()->assertSessionHas('status');
        $this->assertNull($contact->fresh());
    }

    public function test_product_queries_are_answered_publicly_and_deletable(): void
    {
        $query = ProductQuery::where('status', 'open')->firstOrFail();

        $this->actingAs($this->admin)->post("/admin/support/product-queries/{$query->id}/reply", [
            'reply' => 'The listed price includes VAT.',
        ])->assertRedirect()->assertSessionHas('status');
        $this->assertSame('answered', $query->fresh()->status);

        $this->actingAs($this->admin)->delete("/admin/support/product-queries/{$query->id}")
            ->assertRedirect()->assertSessionHas('status');
        $this->assertNull($query->fresh());
    }

    public function test_a_conversation_can_be_joined_and_closed(): void
    {
        $conversation = ProductConversation::firstOrFail();

        $this->actingAs($this->admin)->get("/admin/support/conversations/{$conversation->id}")
            ->assertOk()->assertSee($conversation->title);

        $before = $conversation->messages()->count();
        $this->actingAs($this->admin)->post("/admin/support/conversations/{$conversation->id}/reply", [
            'message' => 'Staff checking in: both parties agreed in chat.',
        ])->assertRedirect()->assertSessionHas('status');
        $this->assertSame($before + 1, $conversation->messages()->count());

        $this->actingAs($this->admin)->post("/admin/support/conversations/{$conversation->id}/status", ['status' => 'closed'])
            ->assertRedirect()->assertSessionHas('status');
        $this->assertSame('closed', $conversation->fresh()->status);

        $this->actingAs($this->admin)->post("/admin/support/conversations/{$conversation->id}/reply", ['message' => 'late'])
            ->assertRedirect()->assertSessionHas('error');
    }
}
