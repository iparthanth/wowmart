<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name'])]
class Attribute extends Model
{
    public function values(): HasMany
    {
        return $this->hasMany(AttributeValue::class);
    }

    /**
     * Products offering this attribute as a variant choice. products.choice_attributes
     * is a JSON array of ids; the storefront's older writer stored them as strings.
     */
    public function productsUsing(): Builder
    {
        return Product::query()->where(fn ($q) => $q
            ->whereJsonContains('choice_attributes', $this->id)
            ->orWhereJsonContains('choice_attributes', (string) $this->id));
    }
}
