<?php

namespace App\Services;

use App\Models\BusinessSetting;
use App\Models\SmsTemplate;
use Illuminate\Support\Facades\DB;

/**
 * Every OTP-system write. Settings live in BusinessSetting; which actors may
 * log in with an OTP is a separate screen from the OTP parameters themselves.
 */
class OtpService
{
    /** Who can authenticate with an OTP (OTP Login Configuration screen). */
    public const LOGIN_TARGETS = [
        'otp_login_customer' => 'Customer login',
        'otp_registration_customer' => 'Customer registration',
        'otp_login_seller' => 'Seller login',
        'otp_registration_seller' => 'Seller registration',
        'otp_login_delivery_boy' => 'Delivery boy login',
        'otp_registration_delivery_boy' => 'Delivery boy registration',
        'otp_login_admin' => 'Admin login',
    ];

    /** OTP parameters (OTP Configurations screen). */
    public const CONFIGURATION_KEYS = [
        'otp_activation' => '1',
        'otp_length' => '6',
        'otp_lifetime_seconds' => '300',
        'otp_max_attempts' => '3',
        'otp_resend_wait_seconds' => '60',
        'otp_gateway' => 'none',
        'otp_gateway_api_key' => '',
        'otp_gateway_sender_id' => '',
    ];

    /** @return array<string, string> */
    public static function loginSettings(): array
    {
        $settings = [];
        foreach (array_keys(self::LOGIN_TARGETS) as $key) {
            $settings[$key] = (string) BusinessSetting::get($key, '0');
        }

        return $settings;
    }

    /** @return array<string, string> */
    public static function configuration(): array
    {
        $settings = [];
        foreach (self::CONFIGURATION_KEYS as $key => $default) {
            $settings[$key] = (string) BusinessSetting::get($key, $default);
        }

        return $settings;
    }

    public function saveLoginSettings(array $data): void
    {
        foreach (array_keys(self::LOGIN_TARGETS) as $key) {
            BusinessSetting::put($key, (string) ($data[$key] ?? '0'));
        }
    }

    public function saveConfiguration(array $data): void
    {
        foreach (self::CONFIGURATION_KEYS as $key => $default) {
            if (array_key_exists($key, $data)) {
                BusinessSetting::put($key, (string) $data[$key]);
            }
        }
    }

    public function updateTemplate(SmsTemplate $template, array $data): SmsTemplate
    {
        return DB::transaction(fn () => tap($template)->update([
            'body' => $data['body'],
            'template_id' => $data['template_id'] ?? null,
            'status' => (bool) ($data['status'] ?? $template->status),
        ]));
    }
}
