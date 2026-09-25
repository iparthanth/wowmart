<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\RefundRequest */
class RefundResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $line = $this->orderDetail;

        return [
            'id' => $this->id,
            'status' => $this->refund_status->value,
            'status_label' => $this->refund_status->label(),
            'refund_amount' => $this->refund_amount,
            'reason' => $this->reason,
            'requested_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'order' => $this->order ? ['id' => $this->order->id, 'code' => $this->order->code] : null,
            'line' => $line ? [
                'id' => $line->id,
                // null when the product has since been deleted
                'product' => $line->product ? ['id' => $line->product->id, 'name' => $line->product->name] : null,
                'variation' => $line->variation,
                'quantity' => $line->quantity,
                'price' => $line->price,
            ] : null,
            'customer' => $this->customer ? [
                'id' => $this->customer->id,
                'name' => $this->customer->name,
                'email' => $this->customer->email,
            ] : null,
            // null seller = in-house line
            'seller' => $this->seller ? [
                'id' => $this->seller->id,
                'name' => $this->seller->name,
                'shop_name' => $this->seller->shop?->name,
            ] : null,
        ];
    }
}
