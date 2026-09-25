<?php

namespace App\Models;

use App\Enums\CouponType;
use App\Enums\DiscountType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * `details` holds the rules specific to each coupon type:
 *   product_base - product_ids
 *   cart_base    - min_buy, max_discount
 *   welcome_base - validation_days, min_buy
 */
#[Fillable(['type', 'code', 'details', 'discount', 'discount_type', 'start_date', 'end_date', 'status'])]
class Coupon extends Model
{
    /** state() value => [label, pill class]. */
    public const STATES = [
        'running' => ['Running', 'pill-ok'],
        'scheduled' => ['Scheduled', 'pill-info'],
        'expired' => ['Expired', 'pill-mute'],
        'off' => ['Switched off', 'pill-mute'],
    ];

    protected function casts(): array
    {
        return [
            'type' => CouponType::class,
            'discount_type' => DiscountType::class,
            'details' => 'array',
            'discount' => 'decimal:2',
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'status' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function isRunning(): bool
    {
        return $this->state() === 'running';
    }

    /** What a shopper would see today: the switch wins, then the date window. */
    public function state(): string
    {
        return match (true) {
            ! $this->status => 'off',
            $this->start_date?->isFuture() ?? false => 'scheduled',
            $this->end_date?->isPast() ?? false => 'expired',
            default => 'running',
        };
    }

    public function discountLabel(): string
    {
        return $this->discount_type === DiscountType::Percent
            ? rtrim(rtrim($this->discount, '0'), '.').'%'
            : '৳'.number_format((float) $this->discount, 2);
    }
}
