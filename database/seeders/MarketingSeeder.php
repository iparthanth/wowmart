<?php

namespace Database\Seeders;

use App\Enums\CouponType;
use App\Enums\DiscountType;
use App\Enums\UserType;
use App\Models\Coupon;
use App\Models\FlashDeal;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

/** One coupon of each of the three captured types, plus a running flash deal. */
class MarketingSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('user_type', UserType::Admin)->firstOrFail();
        $rollIds = Product::where('name', 'like', '%Paper Roll%')->orderBy('id')->limit(3)->pluck('id')->all();

        $coupons = [
            ['type' => CouponType::WelcomeBase, 'code' => 'WOWHELLO', 'discount' => 10, 'discount_type' => DiscountType::Percent,
                'details' => ['validation_days' => 30, 'min_buy' => 0]],
            ['type' => CouponType::CartBase, 'code' => 'WOW100', 'discount' => 100, 'discount_type' => DiscountType::Amount,
                'details' => ['min_buy' => 500, 'max_discount' => 100]],
            ['type' => CouponType::ProductBase, 'code' => 'ROLL15', 'discount' => 15, 'discount_type' => DiscountType::Percent,
                'details' => ['product_ids' => $rollIds]],
        ];

        foreach ($coupons as $c) {
            $coupon = new Coupon($c + ['start_date' => now()->subDay(), 'end_date' => now()->addMonths(3), 'status' => true]);
            $coupon->forceFill(['user_id' => $admin->id])->save();
        }

        $deal = new FlashDeal([
            'title' => 'Weekend Maker Sale',
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(7),
            'background_color' => '#14213D',
            'text_color' => '#FFFFFF',
        ]);
        $deal->forceFill(['slug' => 'weekend-maker-sale', 'status' => true, 'featured' => true])->save();

        $boards = Product::where('name', 'like', '%Arduino%')->orWhere('name', 'like', '%Raspberry%')->pluck('id');
        foreach ($boards as $productId) {
            $deal->products()->attach($productId, ['discount' => 10, 'discount_type' => DiscountType::Percent->value]);
        }

        $this->command?->info(sprintf('Marketing: %d coupons, 1 flash deal with %d products.', count($coupons), $boards->count()));
    }
}
