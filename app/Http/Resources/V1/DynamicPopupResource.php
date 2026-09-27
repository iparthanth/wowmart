<?php

namespace App\Http\Resources\V1;

use App\Models\DynamicPopup;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin DynamicPopup */
class DynamicPopupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'banner' => $this->banner,
            'link' => $this->link,
            'page' => $this->page,
            'once_per_session' => (bool) $this->once_per_session,
            'status' => (bool) $this->status,
        ];
    }
}
