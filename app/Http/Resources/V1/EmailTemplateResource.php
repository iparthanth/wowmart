<?php

namespace App\Http\Resources\V1;

use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin EmailTemplate */
class EmailTemplateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'receiver' => $this->receiver,
            'identifier' => $this->identifier,
            'subject' => $this->subject,
            'body' => $this->body,
            'status' => (bool) $this->status,
        ];
    }
}
