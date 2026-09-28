<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A proof-of-payment for a customer or seller package, verified from the panel. */
#[Fillable(['user_id', 'package_type', 'package_name', 'amount', 'payment_method', 'transaction_id', 'upload_id', 'approval'])]
class OfflinePackagePayment extends Model
{
    public const APPROVALS = ['pending', 'approved', 'rejected'];

    public const TYPES = ['customer', 'seller'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function receipt(): BelongsTo
    {
        return $this->belongsTo(Upload::class, 'upload_id');
    }
}
