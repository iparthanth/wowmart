<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** A named tax (VAT, GST, ...) that products are charged through product_taxes rows. */
#[Fillable(['name', 'status'])]
class Tax extends Model
{
    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    public function productTaxes(): HasMany
    {
        return $this->hasMany(ProductTax::class);
    }

    /** @return Builder<self> only taxes marked active */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }
}
