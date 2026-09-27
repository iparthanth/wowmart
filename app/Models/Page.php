<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * A static storefront page rendered at /page/{slug}. The three standard types
 * (about, terms, privacy) are seeded; the admin adds customs freely.
 */
#[Fillable(['type', 'title', 'slug', 'content', 'meta_title', 'meta_description', 'status'])]
class Page extends Model
{
    public const TYPES = ['about', 'terms', 'privacy', 'custom'];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    /** @return Builder<self> only pages the storefront shows */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }
}
