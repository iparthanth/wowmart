<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** One SMS blast to a list of mobile users. */
#[Fillable(['content', 'template_id', 'recipient_count'])]
class BulkSmsMessage extends Model
{
    /** @return HasMany<BulkSmsRecipient, $this> */
    public function recipients(): HasMany
    {
        return $this->hasMany(BulkSmsRecipient::class);
    }
}
