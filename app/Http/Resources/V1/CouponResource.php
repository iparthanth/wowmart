<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Coupon */
class CouponResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Stored as JSON numbers (the storefront's shape); the API sends money as 2-decimal strings.
        $details = $this->details ?? [];
        foreach (['min_buy', 'max_discount'] as $key) {
            if (isset($details[$key])) {
                $details[$key] = number_format($details[$key], 2, '.', '');
            }
        }

        return [
            'id' => $this->id,
            'type' => $this->type,
            'code' => $this->code,
            'discount' => $this->discount,
            'discount_type' => $this->discount_type,
            'details' => (object) $details, // {} rather than [] when empty
            'start_date' => $this->start_date?->toIso8601String(),
            'end_date' => $this->end_date?->toIso8601String(),
            'status' => $this->status,
            'state' => $this->state(),
            'times_used' => $this->whenCounted('usages'),
        ];
    }
}
