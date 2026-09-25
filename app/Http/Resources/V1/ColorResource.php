<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Color */
class ColorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // ponytail: no product_count - products.colors is JSON, so it costs a scan per colour.
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
        ];
    }
}
