<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * A billing group for carrier ranges: every country in the zone is billed a
 * range's zone price, so a price is set once instead of per country.
 */
#[Fillable(['name', 'status'])]
class Zone extends Model
{
    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    /** @return BelongsToMany<Country, $this> */
    public function countries(): BelongsToMany
    {
        return $this->belongsToMany(Country::class, 'zone_country');
    }

    /** @return Builder<self> only zones marked active */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }
}
