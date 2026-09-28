<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\BusinessSetting;
use App\Models\ManualPaymentMethod;
use App\Models\OfflinePackagePayment;
use App\Models\Order;
use App\Models\User;
use App\Models\WalletRecharge;
use DomainException;
use Illuminate\Support\Facades\DB;

/**
 * Every payment-gateway write: gateway credentials, manual methods, and the
 * approval decisions on offline orders, wallet top-ups and package payments.
 */
class PaymentGatewayService
{
    /** Gateway cards on the Payment Methods screen, with their credential fields. */
    public const GATEWAYS = [
        'paypal' => ['Paypal', ['client_id' => 'Paypal Client Id', 'client_secret' => 'Paypal Client Secret'], true],
        'stripe' => ['Stripe', ['key' => 'Stripe Key', 'secret' => 'Stripe Secret'], false],
        'sslcommerz' => ['Sslcommerz', ['store_id' => 'Sslcz Store Id', 'store_passwd' => 'Sslcz store password'], true],
        'instamojo' => ['Instamojo', ['api_key' => 'API KEY', 'auth_token' => 'AUTH TOKEN'], true],
        'razorpay' => ['Razorpay', ['key' => 'RAZOR KEY', 'secret' => 'RAZOR SECRET'], false],
        'paystack' => ['Paystack', ['public_key' => 'PUBLIC KEY', 'secret_key' => 'SECRET KEY', 'merchant_email' => 'MERCHANT EMAIL', 'currency' => 'PAYSTACK CURRENCY CODE'], false],
        'voguepay' => ['VoguePay', ['merchant_id' => 'MERCHANT ID'], true],
        'payhere' => ['Payhere', ['merchant_id' => 'PAYHERE MERCHANT ID', 'secret' => 'PAYHERE SECRET', 'currency' => 'PAYHERE CURRENCY'], true],
        'ngenius' => ['Ngenius', ['outlet_id' => 'NGENIUS OUTLET ID', 'api_key' => 'NGENIUS API KEY', 'currency' => 'NGENIUS CURRENCY'], false],
        'iyzico' => ['Iyzico', ['api_key' => 'IYZICO API KEY', 'secret_key' => 'IYZICO SECRET KEY', 'currency' => 'IYZICO CURRENCY CODE'], true],
        'nagad' => ['Nagad', ['merchant_id' => 'NAGAD MERCHANT ID', 'private_key' => 'NAGAD PRIVATE KEY'], false],
        'bkash' => ['Bkash', ['app_key' => 'BKASH APP KEY', 'app_secret' => 'BKASH APP SECRET', 'username' => 'BKASH USERNAME', 'password' => 'BKASH PASSWORD'], true],
    ];

    /** What a gateway can be used for (types[] checkboxes per card). */
    public const TYPES = ['checkout', 'wallet', 'customer_package', 'seller_package'];

    public static function settingKey(string $gateway, string $field): string
    {
        return "payment_{$gateway}_{$field}";
    }

    /** @return array<string, mixed> one gateway card's stored values, with defaults */
    public static function gatewaySettings(string $gateway): array
    {
        [, $fields, $hasSandbox] = self::GATEWAYS[$gateway];

        $settings = [];
        foreach (array_keys($fields) as $field) {
            $settings[$field] = (string) BusinessSetting::get(self::settingKey($gateway, $field), '');
        }
        if ($hasSandbox) {
            $settings['sandbox'] = (string) BusinessSetting::get(self::settingKey($gateway, 'sandbox'), '1');
        }
        $settings['types'] = json_decode(
            (string) BusinessSetting::get(self::settingKey($gateway, 'types'), json_encode(self::TYPES)),
            true,
        ) ?? self::TYPES;

        return $settings;
    }

    public function saveGateway(string $gateway, array $data): void
    {
        [, $fields, $hasSandbox] = self::GATEWAYS[$gateway];

        foreach (array_keys($fields) as $field) {
            if (array_key_exists($field, $data)) {
                BusinessSetting::put(self::settingKey($gateway, $field), (string) $data[$field]);
            }
        }
        if ($hasSandbox && isset($data['sandbox'])) {
            BusinessSetting::put(self::settingKey($gateway, 'sandbox'), (string) $data['sandbox']);
        }
        if (isset($data['types'])) {
            BusinessSetting::put(
                self::settingKey($gateway, 'types'),
                json_encode(array_values(array_intersect($data['types'], self::TYPES))),
            );
        }
    }

    // -------------------------------------------------------- manual methods

    public function saveManualMethod(array $data, ?ManualPaymentMethod $method = null): ManualPaymentMethod
    {
        return DB::transaction(fn () => tap($method ?? new ManualPaymentMethod, fn ($m) => $m->fill($data)->save()));
    }

    // ------------------------------------------------------------- decisions

    /** Approving a top-up credits the customer's wallet balance exactly once. */
    public function decideRecharge(WalletRecharge $recharge, string $approval): WalletRecharge
    {
        if ($recharge->approval !== 'pending') {
            throw new DomainException('This recharge request has already been decided.');
        }

        return DB::transaction(function () use ($recharge, $approval): WalletRecharge {
            $recharge->update(['approval' => $approval]);

            if ($approval === 'approved') {
                User::whereKey($recharge->user_id)->increment('wallet_balance', $recharge->amount);
            }

            return $recharge;
        });
    }

    public function decidePackagePayment(OfflinePackagePayment $payment, string $approval): OfflinePackagePayment
    {
        if ($payment->approval !== 'pending') {
            throw new DomainException('This package payment has already been decided.');
        }

        return DB::transaction(fn () => tap($payment)->update(['approval' => $approval]));
    }

    /** Verifying an offline order marks it paid; already-paid orders are refused. */
    public function markOrderPaid(Order $order): Order
    {
        if ($order->payment_status === PaymentStatus::Paid) {
            throw new DomainException('This order is already paid.');
        }

        return app(OrderService::class)->setPaymentStatus($order, PaymentStatus::Paid);
    }
}
