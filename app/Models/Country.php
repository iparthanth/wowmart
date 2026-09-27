<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A country the panel can ship to. Cities and states hang under it; zones
 * group whole countries for carrier billing.
 */
#[Fillable(['name', 'code', 'status'])]
class Country extends Model
{
    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    public function states(): HasMany
    {
        return $this->hasMany(State::class);
    }

    /** @return Builder<self> only countries marked shippable */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }
}
