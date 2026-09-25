<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Brand */
class BrandResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'top' => $this->top,
            'logo' => $this->logoFile ? ['id' => $this->logoFile->id, 'url' => $this->logoFile->url()] : null,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'product_count' => $this->whenCounted('products'),
        ];
    }
}
