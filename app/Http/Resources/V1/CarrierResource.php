<?php

namespace App\Http\Resources\V1;

use App\Models\Carrier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Carrier */
class CarrierResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'logo' => $this->logo,
            'transit_time' => $this->transit_time,
            'free_shipping' => (bool) $this->free_shipping,
            'status' => (bool) $this->status,
            'ranges' => $this->whenLoaded('ranges', fn () => $this->ranges->map(fn ($r) => [
                'id' => $r->id,
                'billing_type' => $r->billing_type,
                'lower' => $r->lower,
                'upper' => $r->upper,
                'prices' => $r->relationLoaded('prices') ? $r->prices->map(fn ($p) => [
                    'zone_id' => $p->zone_id,
                    'price' => $p->price,
                ]) : null,
            ])),
            'ranges_count' => $this->whenCounted('ranges'),
        ];
    }
}
