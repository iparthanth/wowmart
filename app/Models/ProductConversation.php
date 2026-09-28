<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** A buyer-seller chat about one product. */
#[Fillable(['product_id', 'customer_id', 'seller_id', 'title', 'status'])]
class ProductConversation extends Model
{
    public const STATUSES = ['open', 'closed'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /** @return HasMany<ProductConversationMessage, $this> */
    public function messages(): HasMany
    {
        return $this->hasMany(ProductConversationMessage::class)->orderBy('created_at')->orderBy('id');
    }
}
