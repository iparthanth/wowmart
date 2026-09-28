<?php

namespace Tests\Feature\Api\V1;

use App\Models\CustomNotification;
use App\Models\NotificationType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NotificationApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Sanctum::actingAs(User::where('email', 'admin@wowmart.test')->firstOrFail());
    }

    public function test_settings_round_trip(): void
    {
        $this->getJson('/api/v1/notifications/settings')
            ->assertOk()
            ->assertJsonPath('data.show_type', 'design1');

        $this->putJson('/api/v1/notifications/settings', [
            'types' => ['order', 'wallet'],
            'notification_show_type' => 'design3',
        ])->assertOk()->assertJsonPath('data.show_type', 'design3');
    }

    public function test_types_round_trip(): void
    {
        $this->getJson('/api/v1/notifications/types')->assertOk();

        $id = $this->postJson('/api/v1/notifications/types', [
            'name' => 'Back in stock',
            'default_text' => 'A followed product is back!',
            'status' => true,
        ])->assertCreated()->assertJsonPath('data.name', 'Back in stock')->json('data.id');

        $this->putJson("/api/v1/notifications/types/{$id}", [
            'name' => 'Back in stock',
            'default_text' => 'Good news: back in stock.',
            'status' => false,
        ])->assertOk()->assertJsonPath('data.status', false);

        $this->deleteJson("/api/v1/notifications/types/{$id}")->assertNoContent();
        $this->assertDatabaseMissing('notification_types', ['id' => $id]);
    }

    public function test_custom_notifications_are_sent_and_listed(): void
    {
        $customer = User::where('user_type', 'customer')->firstOrFail();
        $type = NotificationType::where('status', 1)->firstOrFail();

        $id = $this->postJson('/api/v1/notifications/custom', [
            'user_ids' => [$customer->id],
            'notification_type_id' => $type->id,
            'content' => 'API announcement',
        ])->assertCreated()->assertJsonPath('data.content', 'API announcement')->json('data.id');

        $this->getJson('/api/v1/notifications/custom')->assertOk();

        $this->deleteJson("/api/v1/notifications/custom/{$id}")->assertNoContent();
        $this->assertModelMissing(CustomNotification::find($id) ?? new CustomNotification);
    }

    public function test_bulk_sms_round_trip(): void
    {
        $this->getJson('/api/v1/bulk-sms')->assertOk();

        $this->postJson('/api/v1/bulk-sms', [
            'user_phones' => ['+8801711000011', '+8801911000033'],
            'content' => 'API SMS blast',
        ])->assertCreated()->assertJsonPath('data.recipient_count', 2);

        $this->assertDatabaseHas('bulk_sms_messages', ['content' => 'API SMS blast']);
    }

    public function test_the_notification_api_needs_an_admin(): void
    {
        Sanctum::actingAs(User::where('email', 'rafi@customers.wowmart.test')->firstOrFail());

        $this->getJson('/api/v1/notifications/settings')->assertForbidden();
        $this->getJson('/api/v1/notifications/types')->assertForbidden();
        $this->postJson('/api/v1/bulk-sms', ['user_phones' => ['1'], 'content' => 'x'])->assertForbidden();
    }
}
