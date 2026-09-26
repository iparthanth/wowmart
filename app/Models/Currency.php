<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * A currency the panel prices in. The system default is kept in
 * BusinessSetting `system_currency` as a currency id.
 */
#[Fillable(['name', 'symbol', 'code', 'exchange_rate', 'status'])]
class Currency extends Model
{
    protected function casts(): array
    {
        return [
            'exchange_rate' => 'decimal:6',
            'status' => 'boolean',
        ];
    }

    /** The currency the whole panel currently prices in, or null before one is chosen. */
    public static function systemCurrency(): ?self
    {
        $id = BusinessSetting::get('system_currency');

        return $id === null ? null : static::find((int) $id);
    }
}
