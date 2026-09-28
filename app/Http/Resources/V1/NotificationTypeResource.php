<?php

namespace App\Http\Resources\V1;

use App\Models\NotificationType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin NotificationType */
class NotificationTypeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'default_text' => $this->default_text,
            'status' => (bool) $this->status,
            'image' => $this->whenLoaded('imageUpload', fn () => $this->imageUpload === null ? null : new UploadResource($this->imageUpload)),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
