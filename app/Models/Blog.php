<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A blog post the storefront renders at /blog/{slug}. */
#[Fillable(['blog_category_id', 'title', 'slug', 'short_description', 'description', 'banner', 'meta_title', 'meta_description', 'status'])]
class Blog extends Model
{
    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    public function bannerFile(): BelongsTo
    {
        return $this->belongsTo(Upload::class, 'banner');
    }

    /** @return Builder<self> only posts the storefront shows */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }
}
