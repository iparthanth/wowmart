<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One search a visitor typed into the storefront search box. */
#[Fillable(['query', 'results', 'user_id'])]
class SearchLog extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
