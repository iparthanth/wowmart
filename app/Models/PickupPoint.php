<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * A place customers can collect their order from. The clone contract keeps the
 * point's contact and address only; which orders await collection there is a
 * delivery module concern (orders_by_pickup_point filters on order details).
 */
#[Fillable(['name', 'phone', 'address', 'status'])]
class PickupPoint extends Model
{
    protected function casts(): array
    {
        return [
            'address' => 'array',
            'status' => 'boolean',
        ];
    }

    /** @return Builder<self> only points marked active */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }
}
