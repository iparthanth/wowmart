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
            OrderSeeder::class,
            ReviewSeeder::class,
            ReportSeeder::class,
            SupportSeeder::class,
        ]);

        // Demo staff and roles fill the Staff screens for people trying the panel.
        // The staff and role tests start from "no staff yet", so they skip it.
        if (! app()->runningUnitTests()) {
            $this->call(StaffSeeder::class);
        }
    }
}
