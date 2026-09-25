<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\Color;
use Illuminate\Database\Seeder;

/**
 * Demo attributes (with values) and colours for the product form; deterministic.
 * Warranties and notes come from CatalogSeeder.
 */
class ProductOptionSeeder extends Seeder
{
    public function run(): void
    {
        $attributes = [
            'Size' => ['Small', 'Medium', 'Large'],
            'Length' => ['1m', '2m', '5m'],
            'Capacity' => ['32GB', '64GB', '128GB'],
        ];

        foreach ($attributes as $name => $values) {
            $attribute = Attribute::create(['name' => $name]);

            foreach ($values as $value) {
                $attribute->values()->create(['value' => $value]);
            }
        }

        $colors = ['Black' => '#1B1B1B', 'White' => '#FFFFFF', 'Red' => '#D7263D', 'Blue' => '#1E5AA8', 'Green' => '#2E8B57'];

        foreach ($colors as $name => $code) {
            Color::create(['name' => $name, 'code' => $code]);
        }
    }
}
