<?php

namespace Tests\Feature\Api\V1;

use App\Models\CustomAlert;
use App\Models\DynamicPopup;
use App\Models\EmailTemplate;
use App\Models\Newsletter;
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MarketingApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Sanctum::actingAs(User::where('email', 'admin@wowmart.test')->firstOrFail());
    }

    public function test_a_popup_round_trips(): void
    {
        $id = $this->postJson('/api/v1/dynamic-popups', [
            'title' => 'API popup', 'page' => 'cart', 'once_per_session' => true, 'status' => true,
        ])->assertCreated()
            ->assertJsonPath('data.title', 'API popup')
            ->assertJsonPath('data.page', 'cart')
            ->json('data.id');

        $this->putJson("/api/v1/dynamic-popups/{$id}", [
            'title' => 'API popup', 'page' => 'home', 'status' => false,
        ])->assertOk()->assertJsonPath('data.page', 'home')->assertJsonPath('data.status', false);

        $this->patchJson("/api/v1/dynamic-popups/{$id}", ['title' => 'x'])->assertMethodNotAllowed();

        $this->deleteJson("/api/v1/dynamic-popups/{$id}")->assertNoContent();
        $this->assertSame(0, DynamicPopup::count());
    }

    public function test_an_alert_round_trips(): void
    {
        $id = $this->postJson('/api/v1/custom-alerts', [
            'title' => 'API alert', 'style' => 'info', 'status' => true,
        ])->assertCreated()->assertJsonPath('data.style', 'info')->json('data.id');

        $this->putJson("/api/v1/custom-alerts/{$id}", [
            'title' => 'API alert', 'style' => 'danger', 'status' => false,
        ])->assertOk()->assertJsonPath('data.style', 'danger');

        $this->deleteJson("/api/v1/custom-alerts/{$id}")->assertNoContent();
        $this->assertSame(0, CustomAlert::count());
    }

    public function test_a_template_round_trips_and_duplicates_are_409(): void
    {
        $id = $this->postJson('/api/v1/email-templates', [
            'receiver' => 'customer', 'identifier' => 'order_placed',
            'subject' => 'Subject', 'body' => 'Body.', 'status' => true,
        ])->assertCreated()->assertJsonPath('data.identifier', 'order_placed')->json('data.id');

        $this->putJson("/api/v1/email-templates/{$id}", [
            'receiver' => 'customer', 'identifier' => 'order_placed',
            'subject' => 'New subject', 'body' => 'Body.', 'status' => false,
        ])->assertOk()->assertJsonPath('data.subject', 'New subject');

        // Another template for the same (receiver, identifier) is refused.
        $this->postJson('/api/v1/email-templates', [
            'receiver' => 'customer', 'identifier' => 'order_placed',
            'subject' => 'Second', 'body' => 'Body.',
        ])->assertConflict();

        $this->deleteJson("/api/v1/email-templates/{$id}")->assertNoContent();
        $this->assertSame(0, EmailTemplate::count());
    }

    public function test_a_template_identifier_must_be_event_shaped(): void
    {
        $this->postJson('/api/v1/email-templates', [
            'receiver' => 'customer', 'identifier' => 'Not An Event',
            'subject' => 'Subject', 'body' => 'Body.',
        ])->assertUnprocessable();
        $this->assertSame(0, EmailTemplate::count());
    }

    public function test_subscribers_are_listed_and_removable(): void
    {
        Subscriber::create(['email' => 'footer@wowmart.test', 'status' => true]);
        $id = Subscriber::firstOrFail()->id;

        $this->getJson('/api/v1/subscribers')
            ->assertOk()
            ->assertJsonPath('data.0.type', 'subscriber')
            ->assertJsonPath('data.0.email', 'footer@wowmart.test');

        $this->deleteJson("/api/v1/subscribers/{$id}")->assertNoContent();
        $this->assertSame(0, Subscriber::count());
    }

    public function test_a_newsletter_is_created_sent_once_and_409_on_a_second_send(): void
    {
        Subscriber::create(['email' => 'footer@wowmart.test', 'status' => true]);

        $id = $this->postJson('/api/v1/newsletters', [
            'subject' => 'October drops', 'body' => 'New stock just landed.', 'segment' => 'subscribers',
        ])->assertCreated()
            ->assertJsonPath('data.type', 'newsletter')
            ->assertJsonPath('data.segment', 'subscribers')
            ->json('data.id');

        $this->assertNull(Newsletter::firstOrFail()->sent_at);

        $this->postJson("/api/v1/newsletters/{$id}/send")
            ->assertOk()->assertJsonPath('data.type', 'newsletter');
        $this->assertNotNull(Newsletter::firstOrFail()->sent_at);

        $this->postJson("/api/v1/newsletters/{$id}/send")->assertConflict();

        $this->deleteJson("/api/v1/newsletters/{$id}")->assertNoContent();
        $this->assertSame(0, Newsletter::count());
    }
}
