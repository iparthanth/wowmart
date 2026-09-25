<?php

namespace App\Services;

use App\Enums\DeliveryStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductStock;
use DomainException;
use Illuminate\Support\Facades\DB;

/**
 * Every change to an order's status goes through here, for the panel and the API.
 *
 * Orders are only ever created by the storefront and never deleted, so status
 * changes are the only writes. Each one re-reads the order under a row lock:
 * two admins (or one double-click) racing to cancel the same order serialise
 * on that lock, and the second sees "already cancelled" and is refused, which
 * is what guarantees stock is returned exactly once.
 */
class OrderService
{
    /** @throws DomainException when DeliveryStatus::canBecome() does not allow the move */
    public function changeDeliveryStatus(Order $order, DeliveryStatus $to): Order
    {
        return DB::transaction(function () use ($order, $to) {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $from = $order->delivery_status;

            if (! $from->canBecome($to)) {
                throw new DomainException($this->refusal($from, $to));
            }

            $changes = ['delivery_status' => $to];

            if ($this->deliveryMeansPaid($order, $to)) {
                $changes['payment_status'] = PaymentStatus::Paid;
            }

            $order->forceFill($changes)->save();
            $order->details()->update(array_map(fn ($status) => $status->value, $changes));

            if ($to === DeliveryStatus::Cancelled) {
                $this->returnStock($order);
            }

            return $order;
        });
    }

    /** @throws DomainException when marking a cancelled order as paid */
    public function setPaymentStatus(Order $order, PaymentStatus $to): Order
    {
        return DB::transaction(function () use ($order, $to) {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($to === PaymentStatus::Paid && $order->delivery_status === DeliveryStatus::Cancelled) {
                throw new DomainException('A cancelled order cannot be marked as paid. If the customer did pay, refund them and leave the order unpaid.');
            }

            $order->forceFill(['payment_status' => $to])->save();
            $order->details()->update(['payment_status' => $to->value]);

            return $order;
        });
    }

    /**
     * BUSINESS DECISION - the owner may want to change this.
     * For cash on delivery the courier collects the money at the door, so the
     * moment a COD order is delivered it counts as paid. If couriers ever remit
     * cash later and staff reconcile by hand, return false here and mark orders
     * paid with setPaymentStatus() instead.
     */
    private function deliveryMeansPaid(Order $order, DeliveryStatus $to): bool
    {
        return $to === DeliveryStatus::Delivered && $order->payment_type === 'cash_on_delivery';
    }

    /**
     * Puts every line back on sale. Increments are done in SQL, not read-modify-
     * write, so a storefront sale of the same product at the same moment is not lost.
     * Lines whose product was deleted are skipped: there is nothing to restock.
     */
    private function returnStock(Order $order): void
    {
        foreach ($order->details()->whereNotNull('product_id')->get() as $line) {
            $qty = (int) $line->quantity;

            Product::whereKey($line->product_id)->update([
                'current_stock' => DB::raw("current_stock + {$qty}"),
                // num_of_sale is unsigned; the CASE keeps it at 0 instead of failing.
                'num_of_sale' => DB::raw("CASE WHEN num_of_sale > {$qty} THEN num_of_sale - {$qty} ELSE 0 END"),
            ]);

            ProductStock::where('product_id', $line->product_id)
                ->where('variant', (string) $line->variation)
                ->increment('qty', $qty);
        }
    }

    private function refusal(DeliveryStatus $from, DeliveryStatus $to): string
    {
        if ($from === $to) {
            return "This order is already {$this->lower($from)}.";
        }

        $message = "This order is {$this->lower($from)}, so it cannot be {$this->verb($to)}.";

        if ($from->isFinal()) {
            return $message.' Delivered and cancelled orders are final.';
        }

        if ($to === DeliveryStatus::Cancelled) {
            return $message.' It is already with the courier: wait for delivery, then handle it as a refund.';
        }

        return $message.' Next it can be: '.implode(' or ', array_map(fn ($s) => $s->label(), $from->next())).'.';
    }

    private function verb(DeliveryStatus $to): string
    {
        return match ($to) {
            DeliveryStatus::Pending => 'moved back to pending',
            DeliveryStatus::Confirmed => 'confirmed',
            DeliveryStatus::Cancelled => 'cancelled',
            default => 'marked as '.$this->lower($to),
        };
    }

    private function lower(DeliveryStatus $s): string
    {
        return strtolower($s->label());
    }
}
