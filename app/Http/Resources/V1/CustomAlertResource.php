<?php

namespace App\Http\Resources\V1;

use App\Models\CustomAlert;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin CustomAlert */
class CustomAlertResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'style' => $this->style,
            'status' => (bool) $this->status,
        ];
    }
}
