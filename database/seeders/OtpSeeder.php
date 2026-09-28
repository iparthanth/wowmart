<?php

namespace Database\Seeders;

use App\Models\BusinessSetting;
use App\Models\SmsTemplate;
use App\Services\OtpService;
use Illuminate\Database\Seeder;

/** SMS templates for the messages the platform sends, plus OTP defaults. */
class OtpSeeder extends Seeder
{
    private const TEMPLATES = [
        ['otp_login', 'OTP login code', 'Your WowMart login code is {code}. It expires in {minutes} minutes.', '100741611111'],
        ['order_placed', 'Order placed', 'Order {order_code} received! We will notify you when it ships. Total ৳{amount}.', '100741622222'],
        ['order_shipped', 'Order shipped', 'Good news! Order {order_code} is on its way. Track it in your WowMart account.', '100741633333'],
        ['refund_approved', 'Refund approved', 'Your refund of ৳{amount} for {order_code} is approved and will reach you shortly.', '100741644444'],
        ['wallet_recharged', 'Wallet recharged', '৳{amount} added to your WowMart wallet. New balance ৳{balance}.', '100741655555'],
    ];

    public function run(): void
    {
        foreach (OtpService::CONFIGURATION_KEYS as $key => $value) {
            BusinessSetting::put($key, $value);
        }

        foreach (self::TEMPLATES as [$key, $name, $body, $templateId]) {
            SmsTemplate::updateOrCreate(['key' => $key], [
                'name' => $name,
                'body' => $body,
                'template_id' => $templateId,
                'status' => true,
            ]);
        }
    }
}
