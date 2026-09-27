<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A customer's offline wallet top-up, approved or rejected from the panel. */
#[Fillable(['user_id', 'amount', 'payment_method', 'transaction_id', 'approval'])]
class WalletRecharge extends Model
{
    public const APPROVALS = ['pending', 'approved', 'rejected'];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
