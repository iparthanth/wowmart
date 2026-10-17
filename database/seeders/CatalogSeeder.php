<?php

namespace Database\Seeders;

use App\Enums\UserType;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Note;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Upload;
use App\Models\User;
use App\Models\Warranty;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * WowMart's demo catalogue, read from storage/app/scrape/catalog.json.
 *
 * Deterministic on purpose - no random numbers anywhere. A fresh seed always
 * gives the same owners, stock levels and sales counts, so a demo or a test can
 * rely on a particular product being sold out or low on stock.
 */
class CatalogSeeder extends Seeder
{
    /** WowMart's own demo sellers. None are copied from the reference site. */
    private const SELLERS = ['Circuit House', 'Sensor Bazaar', 'Receipt Roll Co', 'Maker Den', 'Volt Street'];

    /** Brand => a lowercase fragment that identifies it in a product name. */
    private const BRANDS = [
        'Arduino' => 'arduino',
        'Raspberry Pi' => 'raspberry pi',
        'LEGO' => 'lego',
        'Kingston' => 'a400',
    ];

    /**
     * The rest of the reference panel's brand list (picklymart.com/admin/brands,
     * read 18 Oct 2026), names exactly as listed there. No product matches them.
     */
    private const OTHER_BRANDS = [
        'Apple', 'Audi', 'Baby & me', 'Baby Care', 'Baby TV', 'BMW', 'Canon', 'Dell', 'Demo brand',
        'Demo brand1', 'Denim', 'Dior', 'Ford', 'Honda', 'HP', 'Huwaei', 'Hyundai',
        'Label & Barcode Sticker', 'lucky Brand', 'Nike', 'Nissan', 'One Plus', 'Pampers', 'Pos Roll', 'Tanishq',
    ];

    /** Category => lowercase fragments; first match wins, so order matters. */
    private const CATEGORY_RULES = [
        'Pos Roll' => ['paper roll', 'pos printer'],
        'computer' => ['ssd', 'sata'],
        'Robotics Kits & Boards' => ['arduino', 'raspberry', 'lego', 'chassis'],
        'Tools & Lab Equipment' => ['multimeter', 'soldering', 'goggles', 'calipers', 'glue gun', 'toolset', 'charging station'],
        'Electronic Components & Sensors' => ['sensor', 'module', 'motor driver', 'breadboard', 'jumper', 'led', 'converter', 'display', 'joystick', 'switch'],
    ];

    /** Best sellers in order; they get the highest sale counts. */
    private const BEST_SELLING = [
        '80mm x 80mm POS Printer Paper Roll', 'Arduino UNO Board', 'Raspberry Pi Zero W',
        '56mm x 38mm (2.2 - 1.4 inch) POS Printer Paper Roll', '78mm x 73m Thermal POS Paper Roll',
        'Raspberry Pi 4 Model B – 4GB', 'Arduino Mega 2560 Board',
    ];

    public function run(): void
    {
        $data = json_decode(file_get_contents(storage_path('app/scrape/catalog.json')), true);
        $admin = User::where('user_type', UserType::Admin)->firstOrFail();

        DB::transaction(function () use ($data, $admin) {
            $categories = $this->seedCategories($data['cats']);
            $brands = $this->seedBrands($admin);
            $sellers = $this->seedSellers();
            $this->seedProducts($data['products'], $admin, $categories, $brands, $sellers);
            $this->seedProductOptions();
        });

        $this->command?->info(sprintf(
            'Catalogue: %d categories, %d brands, %d sellers, %d products.',
            Category::count(), Brand::count(), Shop::count(), Product::count(),
        ));
    }

    /** @return array<string, Category> keyed by name */
    private function seedCategories(array $cats): array
    {
        $out = [];

        foreach (array_values($cats) as $i => $cat) {
            $category = new Category(['name' => $cat['name'], 'order_level' => $i, 'featured' => $i < 6]);
            $category->forceFill(['slug' => Str::slug($cat['name']), 'level' => 0])->save();
            $out[$cat['name']] = $category;
        }

        return $out;
    }

    /** @return array<string, Brand> keyed by name */
    private function seedBrands(User $admin): array
    {
        $out = [];

        foreach ([...array_keys(self::BRANDS), ...self::OTHER_BRANDS] as $name) {
            // Logos copied from the reference panel live at uploads/brands/<slug>.<ext>;
            // brands it shows only a placeholder for have none.
            $logo = glob(public_path('uploads/brands/'.Str::slug($name).'.*'))[0] ?? null;
            $upload = $logo ? $this->upload('uploads/brands/'.basename($logo), $name, $admin) : null;

            $brand = new Brand(['name' => $name, 'meta_title' => $name, 'logo' => $upload?->id]);
            $brand->forceFill(['slug' => Brand::uniqueSlug($name)])->save();
            $out[$name] = $brand;
        }

        return $out;
    }

    /** @return list<User> the seller accounts, one shop each */
    private function seedSellers(): array
    {
        $out = [];

        foreach (self::SELLERS as $name) {
            $seller = User::create([
                'name' => $name,
                'email' => Str::slug($name).'@sellers.wowmart.test',
                'password' => 'password',
            ]);
            $seller->forceFill(['user_type' => UserType::Seller])->save();

            $shop = new Shop(['user_id' => $seller->id, 'name' => $name]);
            // The last seller waits for review, so the pending queue has a real row.
            $status = $name === 'Volt Street' ? 'pending' : 'approved';
            $shop->forceFill(['slug' => Str::slug($name), 'verification_status' => $status])->save();

            $out[] = $seller;
        }

        return $out;
    }

    private function seedProducts(array $products, User $admin, array $categories, array $brands, array $sellers): void
    {
        $bestSelling = array_flip(self::BEST_SELLING);

        foreach (array_values($products) as $i => $p) {
            $slug = array_keys($products)[$i];
            $haystack = Str::lower($p['name']);

            // Every fourth product belongs to a seller; the rest are in-house,
            // so both halves of the "In House / Seller" split have real rows.
            $ownedBySeller = $i % 4 === 3;
            $owner = $ownedBySeller ? $sellers[intdiv($i, 4) % count($sellers)] : $admin;

            $stock = match ($i) {
                5 => 0,                       // sold out
                9, 14 => 3,                   // low stock (threshold is 5)
                default => ($i * 37) % 180 + 12,
            };

            $category = $this->match(self::CATEGORY_RULES, $haystack, $categories);
            $brand = $this->match(array_map(fn ($f) => [$f], self::BRANDS), $haystack, $brands);
            $upload = $this->upload($p['local'] ?? null, $p['name'], $admin);

            $product = new Product([
                'name' => $p['name'],
                'category_id' => $category?->id,
                'brand_id' => $brand?->id,
                'unit' => 'pc',
                'min_qty' => 1,
                'unit_price' => (float) $p['price'],
                'current_stock' => $stock,
                'low_stock_quantity' => 5,
                'sku' => sprintf('WM-%05d', $i + 1),
                'thumbnail_img' => $upload?->id,
                'featured' => (bool) $p['todays_deal'],
                'todays_deal' => (bool) $p['todays_deal'],
                'meta_title' => $p['name'],
            ]);

            // System-owned fields: set explicitly, never through fill().
            $product->forceFill([
                'slug' => $slug,
                'added_by' => $ownedBySeller ? 'seller' : 'admin',
                'user_id' => $owner->id,
                'published' => true,
                'approved' => true,
                'wholesale_product' => (bool) $p['wholesale'],
                'num_of_sale' => isset($bestSelling[$p['name']])
                    ? 100 - $bestSelling[$p['name']] * 6
                    : ($i * 7) % 25,
            ])->save();

            if ($category) {
                $product->categories()->attach($category->id);
            }

            if ($upload) {
                $product->gallery()->attach($upload->id, ['sort_order' => 0]);
            }

            // A simple product still gets exactly one stock row, so stock is
            // always read from one place whether or not it has variants.
            $stockRow = $product->stocks()->create([
                'variant' => '',
                'sku' => $product->sku,
                'price' => $product->unit_price,
                'qty' => $stock,
            ]);
        }
    }

    /** The warranty periods and customer notes the product form offers. */
    private function seedProductOptions(): void
    {
        foreach (['6 months', '1 year', '2 years'] as $text) {
            Warranty::create(['text' => $text]);
        }

        Note::create(['type' => 'refund', 'description' => 'Unused items in their original packaging can be returned within 7 days of delivery.']);
        Note::create(['type' => 'warranty', 'description' => 'Covers manufacturing defects only. Physical damage, water damage and burnt components are not covered.']);
    }

    /**
     * First entry in $rules whose fragments appear in $haystack, resolved to a model.
     *
     * @param  array<string, list<string>>  $rules
     * @param  array<string, object>  $models
     */
    private function match(array $rules, string $haystack, array $models): ?object
    {
        foreach ($rules as $name => $fragments) {
            foreach ($fragments as $fragment) {
                if (str_contains($haystack, $fragment)) {
                    return $models[$name] ?? null;
                }
            }
        }

        return null;
    }

    private function upload(?string $path, string $productName, User $admin): ?Upload
    {
        if (! $path || ! is_file(public_path($path))) {
            return null;
        }

        return Upload::firstOrCreate(['file_name' => $path], [
            // Stored files keep their random names; the library shows a readable one.
            'file_original_name' => Str::slug($productName).'.'.pathinfo($path, PATHINFO_EXTENSION),
            'user_id' => $admin->id,
            'file_size' => filesize(public_path($path)),
            'extension' => pathinfo($path, PATHINFO_EXTENSION),
            'type' => 'image',
        ]);
    }
}
