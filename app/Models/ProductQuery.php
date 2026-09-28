<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A customer's question on a product; an admin writes the reply. */
#[Fillable(['user_id', 'product_id', 'question', 'reply', 'status'])]
class ProductQuery extends Model
{
    public const STATUSES = ['open', 'answered'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
