<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Cash one delivery boy collected from customers and handed back to the panel. */
#[Fillable(['delivery_boy_id', 'amount', 'note', 'collected_at'])]
class DeliveryBoyCollection extends Model
{
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'collected_at' => 'datetime',
        ];
    }

    public function deliveryBoy(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
