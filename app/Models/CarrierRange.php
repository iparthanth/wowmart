<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One billing window of a carrier: shipments whose weight (or price, per
 * billing_type) falls in [lower, upper] cost the zone prices below. A null
 * upper is an open window ("and up").
 */
#[Fillable(['carrier_id', 'billing_type', 'lower', 'upper'])]
class CarrierRange extends Model
{
    protected function casts(): array
    {
        return [
            'lower' => 'decimal:2',
            'upper' => 'decimal:2',
        ];
    }

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(Carrier::class);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(CarrierRangePrice::class);
    }
}
