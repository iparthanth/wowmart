<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A customer. Fields are listed one by one, so password and remember_token can
 * never leak even if the model's hidden list changes. password is write-only.
 *
 * @mixin \App\Models\User
 */
class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'country_code' => $this->country_code,
            'phone' => $this->phone,
            'email_verified' => $this->email_verified_at !== null,
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'banned' => (bool) $this->banned,
            'joined_at' => $this->created_at?->toIso8601String(),
            'order_count' => $this->whenCounted('orders'),
            'wishlist_count' => $this->whenCounted('wishlists'),
            'review_count' => $this->whenCounted('reviews'),
            // Orders that were not cancelled.
            'total_spent' => $this->when(array_key_exists('total_spent', $this->resource->getAttributes()),
                fn () => number_format((float) $this->total_spent, 2, '.', '')),
            'addresses' => $this->whenLoaded('addresses', fn () => $this->addresses->map(fn ($a) => [
                'id' => $a->id,
                'name' => $a->name,
                'phone' => $a->phone,
                'address' => $a->address,
                'city' => $a->city,
                'postal_code' => $a->postal_code,
                'default' => $a->set_default,
            ])),
            'recent_orders' => $this->whenLoaded('orders', fn () => $this->orders->map(fn ($o) => [
                'id' => $o->id,
                'code' => $o->code,
                'placed_at' => $o->created_at?->toIso8601String(),
                'grand_total' => $o->grand_total,
                'delivery_status' => $o->delivery_status->value,
                'delivery_status_label' => $o->delivery_status->label(),
                'payment_status' => $o->payment_status->value,
            ])),
        ];
    }
}
