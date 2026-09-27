<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** A newsletter opt-in from the storefront footer. */
#[Fillable(['email', 'status'])]
class Subscriber extends Model
{
    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    /** @return Builder<self> only subscribers currently opted in */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }
}
