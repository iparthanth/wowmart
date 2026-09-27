<?php

namespace Database\Seeders;

use App\Models\BusinessSetting;
use App\Models\Currency;
use App\Models\Tax;
use Illuminate\Database\Seeder;

/**
 * Panel configuration and the currency/tax reference rows.
 *
 * Deterministic like the other seeders, so tests can rely on exact values:
 * four currencies (USD is the system currency) and two active taxes.
 */
class SettingsSeeder extends Seeder
{
    /** name => [symbol, code, rate] */
    private const CURRENCIES = [
        'US Dollar' => ['$', 'USD', 1.0],
        'Euro' => ['€', 'EUR', 0.92],
        'British Pound' => ['£', 'GBP', 0.79],
        'Bangladeshi Taka' => ['৳', 'BDT', 117.0],
    ];

    public function run(): void
    {
        foreach (self::CURRENCIES as $name => [$symbol, $code, $rate]) {
            Currency::firstOrCreate(
                ['code' => $code],
                ['name' => $name, 'symbol' => $symbol, 'code' => $code, 'exchange_rate' => $rate, 'status' => true],
            );
        }

        $usd = Currency::where('code', 'USD')->firstOrFail();
        BusinessSetting::put('system_currency', $usd->id);

        foreach (['VAT', 'GST'] as $name) {
            Tax::firstOrCreate(['name' => $name], ['status' => true]);
        }

        foreach ([
            'site_name' => 'WowMart',
            'site_email' => 'support@wowmart.test',
            'site_phone' => '+1 555 0100',
            'site_address' => '24 Market Street, Springfield',
            'footer_text' => 'WowMart - products, orders and sellers, all in one place.',
            'guest_checkout' => '1',
            'order_code_format' => 'random',
            'order_email_confirmation' => '1',
            'mail_mailer' => 'log',
            'cash_on_delivery' => '1',
            'bank_transfer' => '1',
            'shipping_method' => 'flat',
            'flat_shipping_cost' => '60',
        ] as $key => $value) {
            BusinessSetting::put($key, $value);
        }
    }
}
