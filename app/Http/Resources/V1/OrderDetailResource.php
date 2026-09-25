<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\OrderDetail */
class OrderDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            // null when the product has since been deleted
            'product' => $this->product ? ['id' => $this->product->id, 'name' => $this->product->name, 'slug' => $this->product->slug] : null,
            'variation' => $this->variation,
            'shipping_type' => $this->shipping_type,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'tax' => $this->tax,
            'shipping_cost' => $this->shipping_cost,
            'line_total' => number_format($this->lineTotal(), 2, '.', ''),
            'delivery_status' => $this->delivery_status->value,
            'delivery_status_label' => $this->delivery_status->label(),
            'payment_status' => $this->payment_status->value,
            'payment_status_label' => $this->payment_status->label(),
        ];
    }
}
