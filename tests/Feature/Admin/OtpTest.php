<?php

namespace Tests\Feature\Admin;

use App\Models\BusinessSetting;
use App\Models\SmsTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OtpTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@wowmart.test')->firstOrFail();
    }

    public function test_the_three_otp_screens_render(): void
    {
        $this->actingAs($this->admin)->get('/admin/otp/login-configuration')
            ->assertOk()->assertSee('Customer login');

        $this->actingAs($this->admin)->get('/admin/otp/configuration')
            ->assertOk()->assertSee('Activate OTP');

        $this->actingAs($this->admin)->get('/admin/otp/sms-templates')
            ->assertOk()->assertSee('OTP login code');
    }

    public function test_login_toggles_are_saved(): void
    {
        $this->actingAs($this->admin)->post('/admin/otp/login-configuration', [
            'otp_login_customer' => '1',
            'otp_registration_customer' => '0',
            'otp_login_seller' => '1',
            'otp_registration_seller' => '0',
            'otp_login_delivery_boy' => '0',
            'otp_registration_delivery_boy' => '0',
            'otp_login_admin' => '0',
        ])->assertRedirect()->assertSessionHas('status');

        $this->assertSame('1', BusinessSetting::get('otp_login_customer'));
        $this->assertSame('1', BusinessSetting::get('otp_login_seller'));
        $this->assertSame('0', BusinessSetting::get('otp_registration_customer'));
    }

    public function test_otp_parameters_are_saved(): void
    {
        $this->actingAs($this->admin)->post('/admin/otp/configuration', [
            'otp_activation' => '1',
            'otp_length' => '8',
            'otp_lifetime_seconds' => '600',
            'otp_max_attempts' => '5',
            'otp_resend_wait_seconds' => '30',
            'otp_gateway' => 'twilio',
            'otp_gateway_api_key' => 'sk-test-123',
            'otp_gateway_sender_id' => 'WOWMART',
        ])->assertRedirect()->assertSessionHas('status');

        $this->assertSame('8', BusinessSetting::get('otp_length'));
        $this->assertSame('twilio', BusinessSetting::get('otp_gateway'));
        $this->assertSame('WOWMART', BusinessSetting::get('otp_gateway_sender_id'));
    }

    public function test_sms_templates_are_updated_row_by_row(): void
    {
        $template = SmsTemplate::where('key', 'otp_login')->firstOrFail();

        $this->actingAs($this->admin)->put("/admin/otp/sms-templates/{$template->id}", [
            'body' => 'WowMart verification: {code}',
            'template_id' => '100799999999',
            'status' => '0',
        ])->assertRedirect()->assertSessionHas('status');

        $this->assertSame('WowMart verification: {code}', $template->fresh()->body);
        $this->assertSame('100799999999', $template->fresh()->template_id);
        $this->assertFalse($template->fresh()->status);

        // An absent checkbox keeps the row's stored status.
        $this->actingAs($this->admin)->put("/admin/otp/sms-templates/{$template->id}", [
            'body' => 'WowMart verification: {code}',
            'template_id' => '100799999999',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertFalse($template->fresh()->status);
    }

    public function test_a_template_body_is_required(): void
    {
        $template = SmsTemplate::firstOrFail();

        $this->actingAs($this->admin)->put("/admin/otp/sms-templates/{$template->id}", [
            'body' => '',
        ])->assertSessionHasErrors('body');
    }
}
