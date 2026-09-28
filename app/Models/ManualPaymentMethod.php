<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** An offline payment channel (bank transfer, bKash...) offered at checkout. */
#[Fillable(['name', 'logo', 'instructions', 'status'])]
class ManualPaymentMethod extends Model
{
    protected function casts(): array
    {
        return ['status' => 'boolean'];
    }

    /** The `logo` column holds the upload id; this loads the file itself. */
    public function logoUpload(): BelongsTo
    {
        return $this->belongsTo(Upload::class, 'logo');
    }
}
