<?php

namespace App\Http\Resources\V1;

use App\Models\AffiliateLog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin AffiliateLog */
class AffiliateLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'amount' => (float) $this->amount,
            'description' => $this->description,
            'affiliate' => $this->whenLoaded('affiliate', fn () => [
                'id' => $this->affiliate->id,
                'code' => $this->affiliate->code,
                'name' => $this->affiliate->user?->name,
                'email' => $this->affiliate->user?->email,
            ]),
            'referred_user' => $this->whenLoaded('referredUser', fn () => $this->referredUser === null ? null : [
                'id' => $this->referredUser->id,
                'name' => $this->referredUser->name,
                'email' => $this->referredUser->email,
            ]),
            'order' => $this->whenLoaded('order', fn () => $this->order === null ? null : [
                'id' => $this->order->id,
                'code' => $this->order->code,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
