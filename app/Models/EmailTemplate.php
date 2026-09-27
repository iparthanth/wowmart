<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * What a queued notification mail is built from. One template per
 * (receiver, identifier); the identifier is the event (order_placed, ...).
 */
#[Fillable(['receiver', 'identifier', 'subject', 'body', 'status'])]
class EmailTemplate extends Model
{
    public const RECEIVERS = ['admin', 'seller', 'customer', 'common'];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    /** @return Builder<self> only templates a mail may use */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }
}
