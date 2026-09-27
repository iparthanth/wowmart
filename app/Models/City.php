<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A city inside a shipping state, with the shipping cost area-wise shipping
 * charges for deliveries to it.
 */
#[Fillable(['state_id', 'name', 'cost', 'status'])]
class City extends Model
{
    protected function casts(): array
    {
        return [
            'cost' => 'decimal:2',
            'status' => 'boolean',
        ];
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    /** @return Builder<self> only cities marked active */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }
}
