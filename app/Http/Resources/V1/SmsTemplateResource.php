<?php

namespace App\Http\Resources\V1;

use App\Models\SmsTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin SmsTemplate */
class SmsTemplateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'key' => $this->key,
            'name' => $this->name,
            'body' => $this->body,
            'template_id' => $this->template_id,
            'status' => (bool) $this->status,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
