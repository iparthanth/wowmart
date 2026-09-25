<?php

namespace Database\Seeders;

use App\Enums\DeliveryStatus;
use App\Enums\RefundStatus;
use App\Models\Order;
use App\Models\RefundRequest;
use Illuminate\Database\Seeder;

/**
 * Demo refund requests: one per status, each on the first line of one of the
 * first three delivered orders, so every tab on the refunds screen has a row.
 * Only refund_requests is written; order and stock figures stay as seeded.
 */
class RefundSeeder extends Seeder
{
    /** [status, reason] - one per delivered order, oldest first. */
    private const REQUESTS = [
        [RefundStatus::Pending, 'The box arrived crushed and one item inside is cracked. Photos attached to my message to support.'],
        [RefundStatus::Approved, 'Wrong size delivered; I ordered medium and received large.'],
        [RefundStatus::Rejected, 'Changed my mind after opening it.'],
    ];

    public function run(): void
    {
        $orders = Order::where('delivery_status', DeliveryStatus::Delivered)
            ->with(['details' => fn ($q) => $q->orderBy('id')])
            ->orderBy('id')
            ->limit(count(self::REQUESTS))
            ->get();

        foreach (self::REQUESTS as $i => [$status, $reason]) {
            $order = $orders[$i];
            $line = $order->details->first();
            $requestedAt = $order->created_at->copy()->addDays(3);

            (new RefundRequest([
                'order_id' => $order->id,
                'order_detail_id' => $line->id,
                'user_id' => $order->user_id,
                'seller_id' => $line->seller_id,
                'refund_amount' => round((float) $line->price * $line->quantity, 2),
                'reason' => $reason,
            ]))->forceFill([
                'refund_status' => $status,
                'created_at' => $requestedAt,
                'updated_at' => $status === RefundStatus::Pending ? $requestedAt : $requestedAt->copy()->addDay(),
            ])->save();
        }

        $this->command?->info('Refunds: '.count(self::REQUESTS).' requests, one per status.');
    }
}
