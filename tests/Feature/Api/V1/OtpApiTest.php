<?php

namespace Tests\Feature\Api\V1;

use App\Models\BusinessSetting;
use App\Models\SmsTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OtpApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Sanctum::actingAs(User::where('email', 'admin@wowmart.test')->firstOrFail());
    }

    public function test_login_settings_round_trip(): void
    {
        $this->getJson('/api/v1/otp/login-settings')
            ->assertOk()
            ->assertJsonPath('data.otp_login_customer', '0');

        $this->putJson('/api/v1/otp/login-settings', [
            'otp_login_customer' => '1',
            'otp_registration_customer' => '1',
            'otp_login_seller' => '0',
            'otp_registration_seller' => '0',
            'otp_login_delivery_boy' => '0',
            'otp_registration_delivery_boy' => '0',
            'otp_login_admin' => '0',
        ])->assertOk()->assertJsonPath('data.otp_login_customer', '1');

        $this->assertSame('1', BusinessSetting::get('otp_registration_customer'));
    }

    public function test_configuration_round_trip(): void
    {
        $this->getJson('/api/v1/otp/configuration')->assertOk();

        $this->putJson('/api/v1/otp/configuration', [
            'otp_activation' => '1',
            'otp_length' => '6',
            'otp_lifetime_seconds' => '120',
            'otp_max_attempts' => '2',
            'otp_resend_wait_seconds' => '45',
            'otp_gateway' => 'ssl_wireless',
            'otp_gateway_api_key' => null,
            'otp_gateway_sender_id' => 'WOW',
        ])->assertOk()->assertJsonPath('data.otp_gateway', 'ssl_wireless');

        $this->assertSame('45', BusinessSetting::get('otp_resend_wait_seconds'));

        $this->patchJson('/api/v1/otp/configuration', ['otp_length' => '4'])->assertMethodNotAllowed();
    }

    public function test_sms_templates_round_trip(): void
    {
        $this->getJson('/api/v1/otp/sms-templates')->assertOk()->assertJsonCount(5, 'data');

        $template = SmsTemplate::where('key', 'order_placed')->firstOrFail();

        $this->getJson("/api/v1/otp/sms-templates/{$template->id}")
            ->assertOk()->assertJsonPath('data.key', 'order_placed');

        $this->putJson("/api/v1/otp/sms-templates/{$template->id}", [
            'body' => 'Shipped! {order_code}',
            'template_id' => '100741677777',
            'status' => false,
        ])->assertOk()->assertJsonPath('data.status', false);

        $this->assertSame('Shipped! {order_code}', $template->fresh()->body);
        $this->assertFalse($template->fresh()->status);
    }

    public function test_the_otp_api_needs_an_admin(): void
    {
        Sanctum::actingAs(User::where('email', 'rafi@customers.wowmart.test')->firstOrFail());

        $this->getJson('/api/v1/otp/configuration')->assertForbidden();
        $this->getJson('/api/v1/otp/sms-templates')->assertForbidden();
    }
}
