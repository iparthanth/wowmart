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
            MarketingSeeder::class,
            OrderSeeder::class,
        ]);
    }
}
