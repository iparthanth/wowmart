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
    /** Every value verification_status can hold; a new shop starts pending. */
    public const VERIFICATION_STATUSES = ['pending', 'approved', 'rejected'];

    protected function casts(): array
    {
        return [
            'rating' => 'decimal:2',
            'user_id' => 'integer',
            'logo' => 'integer',
            'num_of_reviews' => 'integer',
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
