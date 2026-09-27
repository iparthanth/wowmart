<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** The sticky header notice; `style` picks how loudly it shows. */
#[Fillable(['title', 'description', 'style', 'status'])]
class CustomAlert extends Model
{
    public const STYLES = ['info', 'warning', 'danger', 'success'];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    /** @return Builder<self> only alerts the storefront shows */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }
}
