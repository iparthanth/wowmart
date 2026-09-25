<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;

/**
 * Reusable text blocks (refund terms, warranty terms) shared across products.
 * The type is fixed once created (NoteService never rewrites it).
 */
#[Fillable(['type', 'description'])]
class Note extends Model
{
    public const TYPES = ['refund' => 'Refund', 'warranty' => 'Warranty'];

    /** ?type=refund|warranty; anything else (unknown, array) means no filter. */
    public static function typeFilter(Request $request): ?string
    {
        $type = $request->query('type');

        return is_string($type) && isset(self::TYPES[$type]) ? $type : null;
    }

    public function refundProducts(): HasMany
    {
        return $this->hasMany(Product::class, 'refund_note_id');
    }

    public function warrantyProducts(): HasMany
    {
        return $this->hasMany(Product::class, 'warranty_note_id');
    }

    /** Needs withCount(['refundProducts', 'warrantyProducts']). */
    public function productsCount(): int
    {
        return (int) $this->refund_products_count + (int) $this->warranty_products_count;
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }
}
