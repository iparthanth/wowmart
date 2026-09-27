<?php

namespace Tests\Feature\Admin;

use App\Models\CustomAlert;
use App\Models\DynamicPopup;
use App\Models\EmailTemplate;
use App\Models\Newsletter;
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketingTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@wowmart.test')->firstOrFail();
    }

    public function test_a_popup_is_created_edited_and_deleted(): void
    {
        $this->actingAs($this->admin)->get('/admin/dynamic-popups')
            ->assertOk()->assertSee('Add a pop-up');

        $this->actingAs($this->admin)->post('/admin/dynamic-popups', [
            'title' => 'Diwali sale', 'description' => 'Save big all week', 'page' => 'all',
            'once_per_session' => '1', 'status' => '1',
        ])->assertRedirect('/admin/dynamic-popups')->assertSessionHasNoErrors();

        $popup = DynamicPopup::where('title', 'Diwali sale')->firstOrFail();
        $this->assertSame('all', $popup->page);

        $this->actingAs($this->admin)->get("/admin/dynamic-popups/{$popup->id}/edit")
            ->assertOk()->assertSee('value="Diwali sale"', false);

        $this->actingAs($this->admin)->put("/admin/dynamic-popups/{$popup->id}", [
            'title' => 'Diwali sale', 'page' => 'home', 'status' => '0',
        ])->assertSessionHasNoErrors();
        $this->assertSame('home', $popup->fresh()->page);
        $this->assertFalse($popup->fresh()->status);

        $this->actingAs($this->admin)->delete("/admin/dynamic-popups/{$popup->id}")
            ->assertRedirect('/admin/dynamic-popups')->assertSessionHas('status');
        $this->assertNull($popup->fresh());
    }

    public function test_a_popup_must_target_a_known_page(): void
    {
        $this->actingAs($this->admin)->post('/admin/dynamic-popups', [
            'title' => 'Lost popup', 'page' => 'checkout-payment',
        ])->assertSessionHasErrors();
        $this->assertSame(0, DynamicPopup::count());
    }

    public function test_a_popup_banner_must_be_a_picture_from_the_library(): void
    {
        $this->actingAs($this->admin)->post('/admin/dynamic-popups', [
            'title' => 'Banner popup', 'page' => 'all', 'banner' => 99999,
        ])->assertSessionHasErrors('banner');

        $this->actingAs($this->admin)->post('/admin/dynamic-popups', [
            'title' => 'Banner popup', 'page' => 'all', 'banner' => 'not-an-id',
        ])->assertSessionHasErrors('banner');

        $this->assertSame(0, DynamicPopup::count());
    }

    public function test_an_alert_is_created_edited_and_deleted(): void
    {
        $this->actingAs($this->admin)->get('/admin/custom-alerts')
            ->assertOk()->assertSee('Add an alert');

        $this->actingAs($this->admin)->post('/admin/custom-alerts', [
            'title' => 'Warehouse closed', 'description' => 'Back on Monday', 'style' => 'warning', 'status' => '1',
        ])->assertRedirect('/admin/custom-alerts')->assertSessionHasNoErrors();

        $alert = CustomAlert::where('title', 'Warehouse closed')->firstOrFail();
        $this->assertSame('warning', $alert->style);

        $this->actingAs($this->admin)->put("/admin/custom-alerts/{$alert->id}", [
            'title' => 'Warehouse closed', 'style' => 'danger', 'status' => '0',
        ])->assertSessionHasNoErrors();
        $this->assertSame('danger', $alert->fresh()->style);

        $this->actingAs($this->admin)->delete("/admin/custom-alerts/{$alert->id}")
            ->assertRedirect('/admin/custom-alerts')->assertSessionHas('status');
        $this->assertNull($alert->fresh());
    }

    public function test_an_alert_must_use_a_known_style(): void
    {
        $this->actingAs($this->admin)->post('/admin/custom-alerts', [
            'title' => 'Odd alert', 'style' => 'purple',
        ])->assertSessionHasErrors();
        $this->assertSame(0, CustomAlert::count());
    }

    public function test_a_template_is_created_edited_and_deleted(): void
    {
        $this->actingAs($this->admin)->get('/admin/email-templates')
            ->assertOk()->assertSee('Add a template');

        $this->actingAs($this->admin)->post('/admin/email-templates', [
            'receiver' => 'customer', 'identifier' => 'order_placed',
            'subject' => 'Your order is in', 'body' => 'Thanks for shopping with us.', 'status' => '1',
        ])->assertRedirect('/admin/email-templates')->assertSessionHasNoErrors();

        $template = EmailTemplate::where('identifier', 'order_placed')->firstOrFail();
        $this->assertSame('customer', $template->receiver);

        $this->actingAs($this->admin)->get("/admin/email-templates/{$template->id}/edit")
            ->assertOk()->assertSee('value="order_placed"', false);

        $this->actingAs($this->admin)->put("/admin/email-templates/{$template->id}", [
            'receiver' => 'customer', 'identifier' => 'order_placed',
            'subject' => 'Your WowMart order is in', 'body' => 'Thanks for shopping with us.', 'status' => '0',
        ])->assertSessionHasNoErrors();
        $this->assertSame('Your WowMart order is in', $template->fresh()->subject);
        $this->assertFalse($template->fresh()->status);

        $this->actingAs($this->admin)->delete("/admin/email-templates/{$template->id}")
            ->assertRedirect('/admin/email-templates')->assertSessionHas('status');
        $this->assertNull($template->fresh());
    }

    public function test_a_template_cannot_shadow_another_for_the_same_event(): void
    {
        $this->actingAs($this->admin)->post('/admin/email-templates', [
            'receiver' => 'seller', 'identifier' => 'order_placed',
            'subject' => 'First', 'body' => 'Body.', 'status' => '1',
        ])->assertSessionHasNoErrors();

        $this->actingAs($this->admin)->post('/admin/email-templates', [
            'receiver' => 'seller', 'identifier' => 'order_placed',
            'subject' => 'Second', 'body' => 'Body.', 'status' => '1',
        ])->assertRedirect()->assertSessionHas('error');
        $this->assertSame(1, EmailTemplate::count());

        // A different receiver may cover the same event.
        $this->actingAs($this->admin)->post('/admin/email-templates', [
            'receiver' => 'admin', 'identifier' => 'order_placed',
            'subject' => 'Admin copy', 'body' => 'Body.', 'status' => '1',
        ])->assertSessionHasNoErrors();
        $this->assertSame(2, EmailTemplate::count());
    }

    public function test_an_invalid_identifier_is_rejected(): void
    {
        $this->actingAs($this->admin)->post('/admin/email-templates', [
            'receiver' => 'customer', 'identifier' => 'Order Placed!',
            'subject' => 'Subject', 'body' => 'Body.',
        ])->assertSessionHasErrors('identifier');
        $this->assertSame(0, EmailTemplate::count());
    }

    public function test_a_subscriber_is_listed_and_removed(): void
    {
        $subscriber = Subscriber::create(['email' => 'footer@wowmart.test', 'status' => true]);

        $this->actingAs($this->admin)->get('/admin/subscribers')
            ->assertOk()->assertSee('footer@wowmart.test');

        $this->actingAs($this->admin)->delete("/admin/subscribers/{$subscriber->id}")
            ->assertRedirect('/admin/subscribers')->assertSessionHas('status');
        $this->assertNull($subscriber->fresh());
    }

    public function test_a_newsletter_is_composed_sent_once_and_deleted(): void
    {
        Subscriber::create(['email' => 'footer@wowmart.test', 'status' => true]);

        $this->actingAs($this->admin)->get('/admin/newsletters')
            ->assertOk()->assertSee('Compose a newsletter');

        $this->actingAs($this->admin)->post('/admin/newsletters', [
            'subject' => 'October drops', 'body' => 'New stock just landed.', 'segment' => 'subscribers',
        ])->assertRedirect('/admin/newsletters')->assertSessionHasNoErrors();

        $newsletter = Newsletter::where('subject', 'October drops')->firstOrFail();
        $this->assertNull($newsletter->sent_at);

        $this->actingAs($this->admin)->post("/admin/newsletters/{$newsletter->id}/send")
            ->assertRedirect('/admin/newsletters')->assertSessionHas('status');
        $this->assertNotNull($newsletter->fresh()->sent_at);

        // A sent newsletter can never go out twice.
        $this->actingAs($this->admin)->post("/admin/newsletters/{$newsletter->id}/send")
            ->assertRedirect()->assertSessionHas('error');

        $this->actingAs($this->admin)->delete("/admin/newsletters/{$newsletter->id}")
            ->assertRedirect('/admin/newsletters')->assertSessionHas('status');
        $this->assertNull($newsletter->fresh());
    }

    public function test_a_newsletter_needs_a_known_segment(): void
    {
        $this->actingAs($this->admin)->post('/admin/newsletters', [
            'subject' => 'Bad segment', 'body' => 'Body.', 'segment' => 'sellers',
        ])->assertSessionHasErrors();
        $this->assertSame(0, Newsletter::count());
    }
}
