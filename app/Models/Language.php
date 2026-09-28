<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** One language the panel and storefront can be shown in. */
#[Fillable(['name', 'code', 'rtl', 'status'])]
class Language extends Model
{
    protected function casts(): array
    {
        return [
            'rtl' => 'boolean',
            'status' => 'boolean',
        ];
    }

    /** Languages the storefront can actually switch to. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }
}
