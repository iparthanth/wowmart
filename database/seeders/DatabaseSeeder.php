<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /** Order matters: products need the admin, orders need products. */
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            CatalogSeeder::class,
            ProductOptionSeeder::class,
            MarketingSeeder::class,
            OrderSeeder::class,
            ReviewSeeder::class,
            RefundSeeder::class,
            SettingsSeeder::class,
            ShippingSeeder::class,
            PageSeeder::class,
            BlogSeeder::class,
            DeliveryBoySeeder::class,
            ReportSeeder::class,
            LanguageSeeder::class,
            SupportSeeder::class,
            AffiliateSeeder::class,
            OtpSeeder::class,
            PaymentSeeder::class,
        ]);
    }
}
