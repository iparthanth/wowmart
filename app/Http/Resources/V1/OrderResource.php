<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Order */
class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $money = fn (float $v) => number_format($v, 2, '.', '');

        return [
            'id' => $this->id,
            'code' => $this->code,
            'placed_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'delivery_status' => $this->delivery_status->value,
            'delivery_status_label' => $this->delivery_status->label(),
            'payment_status' => $this->payment_status->value,
            'payment_status_label' => $this->payment_status->label(),
            'payment_type' => $this->payment_type,
            'payment_type_label' => $this->paymentMethodLabel(),
            'grand_total' => $this->grand_total,
            'coupon_code' => $this->coupon_code,
            'coupon_discount' => $this->coupon_discount,
            'item_count' => $this->whenCounted('details'),
            'customer' => $this->whenLoaded('customer', fn () => $this->customer ? [
                'id' => $this->customer->id,
                'name' => $this->customer->name,
                'email' => $this->customer->email,
                'phone' => $this->customer->phone,
            ] : null),
            // null seller = in-house order
            'seller' => $this->whenLoaded('seller', fn () => $this->seller ? [
                'id' => $this->seller->id,
                'name' => $this->seller->name,
                'shop_name' => $this->seller->shop?->name,
            ] : null),
            'shipping_address' => $this->when($this->relationLoaded('details'), fn () => $this->shipping_address),
            'subtotal' => $this->when($this->relationLoaded('details'), fn () => $money($this->subtotal())),
            'tax' => $this->when($this->relationLoaded('details'), fn () => $money($this->taxTotal())),
            'shipping_cost' => $this->when($this->relationLoaded('details'), fn () => $money($this->shippingTotal())),
            'lines' => OrderDetailResource::collection($this->whenLoaded('details')),
        ];
    }
}
