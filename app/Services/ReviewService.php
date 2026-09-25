<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Support\Facades\DB;

/**
 * Review moderation for the panel and the API. Every change here moves
 * products.rating, so each write recomputes it in the same transaction.
 */
class ReviewService
{
    /** Sets exactly the given flags (only `status`), so repeating a request changes nothing. */
    public function setFlags(Review $review, array $flags): Review
    {
        return DB::transaction(function () use ($review, $flags) {
            $review = Review::lockForUpdate()->findOrFail($review->id);

            if (array_key_exists('status', $flags)) {
                $review->forceFill(['status' => (bool) $flags['status']])->save();
            }

            $this->refreshRating($review->product_id);

            return $review;
        });
    }

    /** No foreign key points at a review, so nothing else can go blank. */
    public function delete(Review $review): void
    {
        DB::transaction(function () use ($review) {
            $review = Review::lockForUpdate()->findOrFail($review->id);
            $review->delete();

            $this->refreshRating($review->product_id);
        });
    }

    /**
     * The storefront's Product::refreshRating rule: the average of VISIBLE
     * reviews to 2 decimals, 0 when there are none. Call inside a transaction;
     * the product row lock serialises concurrent moderation of the same product.
     */
    public function refreshRating(int $productId): void
    {
        $product = Product::lockForUpdate()->find($productId);

        if (! $product) {
            return;
        }

        // reorder(): MySQL's ONLY_FULL_GROUP_BY rejects an aggregate ordered by id.
        $average = $product->reviews()->where('status', true)->reorder()->avg('rating');

        $product->forceFill(['rating' => round((float) $average, 2)])->save();
    }
}
