<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** A seller (user) with their shop. @mixin \App\Models\User */
class SellerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $shop = $this->shop;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'banned' => (bool) $this->banned,
            'shop' => $shop ? [
                'id' => $shop->id,
                'name' => $shop->name,
                'slug' => $shop->slug,
                'logo' => $shop->logoFile ? ['id' => $shop->logoFile->id, 'url' => $shop->logoFile->url()] : null,
                'address' => $shop->address,
                'phone' => $shop->phone,
                'rating' => $shop->rating,
                'num_of_reviews' => $shop->num_of_reviews,
                'verification_status' => $shop->verification_status,
            ] : null,
            // Subquery counts come back as strings from some PDO drivers.
            'product_count' => (int) $this->products_count,
            'order_line_count' => (int) $this->order_lines_count,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
