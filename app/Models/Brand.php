<?php

namespace App\Models;

use App\Models\Concerns\HasUniqueSlug;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'logo', 'top', 'meta_title', 'meta_description'])]
class Brand extends Model
{
    use HasUniqueSlug;

    protected function casts(): array
    {
        return [
            'top' => 'boolean',
        ];
    }

    public function logoFile(): BelongsTo
    {
        return $this->belongsTo(Upload::class, 'logo');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
