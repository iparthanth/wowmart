<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** A blog category grouping the storefront's posts. */
#[Fillable(['name', 'slug', 'status'])]
class BlogCategory extends Model
{
    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Blog::class, 'blog_category_id');
    }

    /** @return Builder<self> only categories the storefront shows */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }
}
