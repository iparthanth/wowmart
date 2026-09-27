<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A pop-up window the storefront shows; `page` narrows it to one department. */
#[Fillable(['title', 'description', 'banner', 'link', 'page', 'once_per_session', 'status'])]
class DynamicPopup extends Model
{
    public const PAGES = ['all', 'home', 'products', 'cart', 'checkout'];

    protected function casts(): array
    {
        return [
            'once_per_session' => 'boolean',
            'status' => 'boolean',
        ];
    }

    public function bannerFile(): BelongsTo
    {
        return $this->belongsTo(Upload::class, 'banner');
    }

    /** @return Builder<self> only pop-ups the storefront shows */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }
}
