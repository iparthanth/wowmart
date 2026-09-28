<?php

namespace App\Http\Resources\V1;

use App\Models\AffiliateReferral;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin AffiliateReferral */
class AffiliateReferralResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'referred_user' => $this->whenLoaded('referredUser', fn () => [
                'id' => $this->referredUser->id,
                'name' => $this->referredUser->name,
                'email' => $this->referredUser->email,
                'phone' => $this->referredUser->phone,
            ]),
            'affiliate' => $this->whenLoaded('affiliate', fn () => [
                'id' => $this->affiliate->id,
                'code' => $this->affiliate->code,
                'name' => $this->affiliate->user?->name,
                'email' => $this->affiliate->user?->email,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
