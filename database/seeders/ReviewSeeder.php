<?php

namespace Database\Seeders;

use App\Enums\DeliveryStatus;
use App\Models\Review;
use App\Services\ReviewService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Demo product reviews. Deterministic, and only verified purchases: a customer
 * reviews a product only if it is on one of their DELIVERED orders.
 * With the order seed that is 9 reviews, one of them hidden.
 */
class ReviewSeeder extends Seeder
{
    private const RATINGS = [5, 4, 5, 3, 4, 2, 5, 4, 3];

    private const COMMENTS = [
        'Arrived well packed and works exactly as described.',
        'Good quality for the price. Delivery took a day longer than promised.',
        'Used it in a weekend build, no problems at all. Would buy again.',
        'Does the job, but the pins needed a little straightening.',
        'Solid part. The seller answered my wiring question quickly.',
        'Call 01700-000000 for cheaper wholesale prices!!!', // the spam one the admin hid
        'Exactly what my workshop needed. Genuine part, fast delivery.',
        'Works fine. Documentation could be better for beginners.',
        'Average. It works, though the finish is rougher than the photos.',
    ];

    /** Index into the list above of the review that is seeded hidden. */
    private const HIDDEN = 5;

    public function run(ReviewService $service): void
    {
        $lines = DB::table('order_details')
            ->join('orders', 'orders.id', '=', 'order_details.order_id')
            ->join('users', 'users.id', '=', 'orders.user_id')
            ->where('orders.delivery_status', DeliveryStatus::Delivered->value)
            ->where('users.email', 'like', '%@customers.wowmart.test')
            ->orderBy('orders.id')
            ->orderBy('order_details.id')
            ->get(['order_details.product_id', 'orders.user_id', 'orders.created_at']);

        $n = 0;
        $seen = [];

        foreach ($lines as $line) {
            $key = $line->product_id.'-'.$line->user_id;

            if (isset($seen[$key]) || $n >= count(self::RATINGS)) {
                continue; // one review per product and customer
            }
            $seen[$key] = true;

            $at = Carbon::parse($line->created_at)->addDays(2); // after delivery

            (new Review([
                'product_id' => $line->product_id,
                'user_id' => $line->user_id,
                'rating' => self::RATINGS[$n],
                'comment' => self::COMMENTS[$n],
            ]))->forceFill(['status' => $n !== self::HIDDEN, 'created_at' => $at, 'updated_at' => $at])->save();

            $n++;
        }

        DB::transaction(function () use ($service, $lines) {
            foreach ($lines->pluck('product_id')->unique() as $productId) {
                $service->refreshRating((int) $productId); // 0 for delivered products nobody reviewed
            }
        });

        $this->command?->info("Reviews: {$n} verified-purchase reviews, 1 hidden.");
    }
}
