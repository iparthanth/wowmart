<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'code'])]
class Color extends Model
{
    /** Products offering this colour. products.colors is a JSON array of hex codes, in whatever case the writer used. */
    public function productsUsing(): Builder
    {
        return Product::query()->where(fn ($q) => $q
            ->whereJsonContains('colors', strtoupper($this->code))
            ->orWhereJsonContains('colors', strtolower($this->code)));
    }
}
