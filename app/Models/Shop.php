<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * verification_status, rating and review counts are changed by admin actions
 * and by the review system, so none of them are mass assignable.
 */
#[Fillable(['user_id', 'name', 'logo', 'address', 'phone'])]
class Shop extends Model
{
    protected function casts(): array
    {
        return [
            'rating' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function logoFile(): BelongsTo
    {
        return $this->belongsTo(Upload::class, 'logo');
    }
}
