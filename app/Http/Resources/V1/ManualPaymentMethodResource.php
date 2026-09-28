<?php

namespace App\Http\Resources\V1;

use App\Models\ManualPaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ManualPaymentMethod */
class ManualPaymentMethodResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'instructions' => $this->instructions,
            'status' => (bool) $this->status,
            'logo' => $this->whenLoaded('logoUpload', fn () => $this->logoUpload === null ? null : new UploadResource($this->logoUpload)),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
