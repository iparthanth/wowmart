<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\User
 * A panel member: an administrator or a staff member with a role.
 */
class StaffResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'type' => $this->user_type->value,
            'role' => $this->when($this->role_id !== null && $this->relationLoaded('role'), fn () => [
                'id' => $this->role->id,
                'name' => $this->role->name,
            ]),
            'banned' => $this->banned,
        ];
    }
}
