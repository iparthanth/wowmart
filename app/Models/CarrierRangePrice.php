<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * What a carrier range charges for one shipping zone: every country in the
 * zone is billed this price for shipments inside the range.
 */
#[Fillable(['carrier_range_id', 'zone_id', 'price'])]
class CarrierRangePrice extends Model
{
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

    public function range(): BelongsTo
    {
        return $this->belongsTo(CarrierRange::class, 'carrier_range_id');
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }
}
