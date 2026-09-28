<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One phone number on an SMS blast, with its delivery outcome. */
#[Fillable(['bulk_sms_message_id', 'user_id', 'phone', 'status'])]
class BulkSmsRecipient extends Model
{
    public function message(): BelongsTo
    {
        return $this->belongsTo(BulkSmsMessage::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
