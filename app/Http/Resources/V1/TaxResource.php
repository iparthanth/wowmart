<?php

namespace App\Http\Resources\V1;

use App\Models\Tax;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Tax */
class TaxResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => (bool) $this->status,
            'product_tax_count' => (int) ($this->product_taxes_count ?? 0),
        ];
    }
}
