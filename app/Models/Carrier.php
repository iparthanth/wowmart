<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A shipping company. Billing is set up as ranges (weight or price windows);
 * each range prices the shipping zones it covers.
 */
#[Fillable(['name', 'logo', 'transit_time', 'free_shipping', 'status'])]
class Carrier extends Model
{
    protected function casts(): array
    {
        return [
            'free_shipping' => 'boolean',
            'status' => 'boolean',
        ];
    }

    public function ranges(): HasMany
    {
        return $this->hasMany(CarrierRange::class);
    }

    /** @return Builder<self> only carriers marked active */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }
}
