<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One payout to a delivery boy: money the panel paid them. */
#[Fillable(['delivery_boy_id', 'amount', 'note', 'paid_at'])]
class DeliveryBoyPayment extends Model
{
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function deliveryBoy(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
