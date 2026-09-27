<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The marketplace's cut of one paid seller order, written once when the order
 * is paid; `order_code` is a snapshot so the row keeps its reference.
 */
#[Fillable(['order_id', 'order_code', 'seller_id', 'admin_commission', 'seller_earning'])]
class CommissionLog extends Model
{
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }
}
