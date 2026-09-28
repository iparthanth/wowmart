<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A request to pay an affiliate's earned balance out. */
#[Fillable(['affiliate_id', 'amount', 'status', 'note'])]
class AffiliateWithdrawal extends Model
{
    public const STATUSES = ['pending', 'approved', 'rejected'];

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }
}
