<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\FlashDeal */
class FlashDealResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'page_url' => $this->pageUrl(),
            'start_date' => $this->start_date?->toIso8601String(),
            'end_date' => $this->end_date?->toIso8601String(),
            'status' => $this->status,
            'featured' => $this->featured,
            'background_color' => $this->background_color,
            'text_color' => $this->text_color,
            'banner' => $this->bannerFile ? ['id' => $this->bannerFile->id, 'url' => $this->bannerFile->url()] : null,
            'state' => $this->state(),
            'product_count' => $this->whenCounted('products'),
            'products' => $this->whenLoaded('products', fn () => $this->products->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'unit_price' => $p->unit_price,
                // The pivot has no cast; keep the API's money-as-2-decimal-string contract.
                'discount' => number_format((float) $p->pivot->discount, 2, '.', ''),
                'discount_type' => $p->pivot->discount_type,
            ])->values()),
        ];
    }
}
