<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** A customer's support ticket with the panel; the thread lives in replies. */
#[Fillable(['user_id', 'subject', 'status'])]
class SupportTicket extends Model
{
    public const STATUSES = ['open', 'closed'];

    protected static function booted(): void
    {
        static::creating(function (self $ticket): void {
            $ticket->code ??= 'TKT-'.str_pad((string) (static::max('id') + 1), 6, '0', STR_PAD_LEFT);
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<SupportTicketReply, $this> */
    public function replies(): HasMany
    {
        return $this->hasMany(SupportTicketReply::class)->orderBy('created_at')->orderBy('id');
    }
}
