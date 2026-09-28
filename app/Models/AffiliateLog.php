<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One entry on an affiliate's money trail: commission earned or withdrawal paid. */
#[Fillable(['affiliate_id', 'referred_user_id', 'order_id', 'type', 'amount', 'description'])]
class AffiliateLog extends Model
{
    public const TYPES = ['registration', 'purchase', 'withdrawal'];

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function referredUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
