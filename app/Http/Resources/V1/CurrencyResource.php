<?php

namespace App\Http\Resources\V1;

use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Currency */
class CurrencyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'symbol' => $this->symbol,
            'code' => $this->code,
            'exchange_rate' => (float) $this->exchange_rate,
            'status' => (bool) $this->status,
        ];
    }
}
