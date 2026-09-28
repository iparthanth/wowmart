<?php

namespace Tests\Feature\Admin;

use App\Models\BulkSmsMessage;
use App\Models\BusinessSetting;
use App\Models\CustomNotification;
use App\Models\NotificationType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@wowmart.test')->firstOrFail();
    }

    public function test_the_five_notification_screens_render(): void
    {
        $this->actingAs($this->admin)->get('/admin/notifications/settings')
            ->assertOk()->assertSee('Notification design');

        $this->actingAs($this->admin)->get('/admin/notifications/types')
            ->assertOk()->assertSee('Order placed');

        $this->actingAs($this->admin)->get('/admin/notifications/custom')
            ->assertOk()->assertSee('Send a custom notification');

        $this->actingAs($this->admin)->get('/admin/notifications/custom/history')
            ->assertOk()->assertSee('Weekend flash sale');

        $this->actingAs($this->admin)->get('/admin/bulk-sms')
            ->assertOk()->assertSee('Send bulk SMS');
    }

    public function test_notification_settings_are_saved(): void
    {
        $this->actingAs($this->admin)->post('/admin/notifications/settings', [
            'types' => ['order', 'promotion'],
            'notification_show_type' => 'design2',
        ])->assertRedirect()->assertSessionHas('status');

        $settings = NotificationTest::readSettings();
        $this->assertSame(['order', 'promotion'], $settings['types']);
        $this->assertSame('design2', $settings['show_type']);
    }

    private static function readSettings(): array
    {
        return [
            'types' => json_decode((string) BusinessSetting::get('notification_types'), true),
            'show_type' => (string) BusinessSetting::get('notification_show_type'),
        ];
    }

    public function test_notification_types_crud(): void
    {
        $this->actingAs($this->admin)->post('/admin/notifications/types', [
            'name' => 'Price drop',
            'default_text' => 'A product you follow just got cheaper.',
            'status' => '1',
        ])->assertRedirect()->assertSessionHas('status');

        $type = NotificationType::where('name', 'Price drop')->firstOrFail();

        $this->actingAs($this->admin)->get("/admin/notifications/types/{$type->id}/edit")
            ->assertOk()->assertSee('Price drop');

        $this->actingAs($this->admin)->put("/admin/notifications/types/{$type->id}", [
            'name' => 'Price drop alert',
            'default_text' => $type->default_text,
            'status' => '0',
        ])->assertRedirect()->assertSessionHas('status');
        $this->assertSame('Price drop alert', $type->fresh()->name);
        $this->assertFalse($type->fresh()->status);

        $this->actingAs($this->admin)->delete("/admin/notifications/types/{$type->id}")
            ->assertRedirect()->assertSessionHas('status');
        $this->assertNull($type->fresh());
    }

    public function test_a_type_with_sent_announcements_cannot_be_deleted(): void
    {
        $type = NotificationType::whereHas('customNotifications')->firstOrFail();

        $this->actingAs($this->admin)->delete("/admin/notifications/types/{$type->id}")
            ->assertRedirect()->assertSessionHas('error');
        $this->assertNotNull($type->fresh());
    }

    public function test_a_custom_notification_is_sent_and_histories_can_be_removed(): void
    {
        $customer = User::where('user_type', 'customer')->firstOrFail();
        $type = NotificationType::where('status', 1)->firstOrFail();

        $this->actingAs($this->admin)->post('/admin/notifications/custom', [
            'user_ids' => [$customer->id],
            'notification_type_id' => $type->id,
            'content' => 'Your coupon expires tomorrow!',
            'link' => '/campaign/coupon-week',
        ])->assertRedirect()->assertSessionHas('status');

        $notification = CustomNotification::where('content', 'Your coupon expires tomorrow!')->firstOrFail();
        $this->assertSame(1, $notification->users()->count());

        $this->actingAs($this->admin)->delete("/admin/notifications/custom/{$notification->id}")
            ->assertRedirect()->assertSessionHas('status');
        $this->assertNull($notification->fresh());
    }

    public function test_a_custom_send_without_users_is_refused(): void
    {
        $type = NotificationType::where('status', 1)->firstOrFail();

        $this->actingAs($this->admin)->post('/admin/notifications/custom', [
            'user_ids' => [],
            'notification_type_id' => $type->id,
            'content' => 'Nobody gets this.',
        ])->assertSessionHasErrors('user_ids');
    }

    public function test_bulk_sms_is_sent_to_unique_numbers(): void
    {
        $this->actingAs($this->admin)->post('/admin/bulk-sms', [
            'user_phones' => "+8801711000011\n+8801811000022\n+8801711000011",
            'content' => 'WowMart: free shipping this weekend only.',
            'template_id' => '100741690002',
        ])->assertRedirect()->assertSessionHas('status');

        $message = BulkSmsMessage::where('template_id', '100741690002')->firstOrFail();
        $this->assertSame(2, $message->recipients()->count()); // duplicate removed

        $this->actingAs($this->admin)->post('/admin/bulk-sms', [
            'user_phones' => [],
            'content' => 'Empty.',
        ])->assertSessionHasErrors('user_phones');
    }
}
