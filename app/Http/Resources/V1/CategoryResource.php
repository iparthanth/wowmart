<?php

namespace App\Http\Resources\V1;

use App\Models\Upload;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Category */
class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $file = fn (?Upload $u) => $u ? ['id' => $u->id, 'url' => $u->url()] : null;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'parent_id' => $this->parent_id,
            'level' => $this->level,
            'order_level' => $this->order_level,
            'featured' => $this->featured,
            'digital' => $this->digital,
            'icon' => $file($this->iconFile),
            'banner' => $file($this->bannerFile),
            'cover_image' => $file($this->coverFile),
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'product_count' => $this->whenCounted('products'),
        ];
    }
}
