<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One product's charge under one tax: an amount plus how it applies.
 * `tax` is a plain amount; `tax_type` says whether it is read as percent or flat.
 */
#[Fillable(['product_id', 'tax_id', 'tax', 'tax_type'])]
class ProductTax extends Model
{
    public const TYPES = ['percent', 'flat'];

    protected function casts(): array
    {
        return [
            'tax' => 'decimal:2',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class);
    }
}
