<?php

namespace App\Models;

use App\Enums\DeliveryStatus;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['order_id', 'seller_id', 'product_id', 'variation', 'price', 'tax', 'shipping_cost', 'quantity', 'shipping_type'])]
class OrderDetail extends Model
{
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'tax' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'delivery_status' => DeliveryStatus::class,
            'payment_status' => PaymentStatus::class,
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function lineTotal(): float
    {
        return round((float) $this->price * $this->quantity + (float) $this->tax + (float) $this->shipping_cost, 2);
    }
}
