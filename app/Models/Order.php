<?php

namespace App\Models;

use App\Enums\DeliveryStatus;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * `code`, `delivery_status` and `payment_status` are not fillable: the code is
 * generated once, and both statuses may only move through their own transitions
 * (App\Services\OrderService).
 */
#[Fillable(['user_id', 'seller_id', 'shipping_address', 'payment_type', 'grand_total', 'coupon_discount', 'coupon_code'])]
class Order extends Model
{
    /** Sort key => [column, direction]. Anything else falls back to newest. */
    public const SORTS = [
        'newest' => ['id', 'desc'],
        'oldest' => ['id', 'asc'],
        'amount' => ['grand_total', 'desc'],
    ];

    protected function casts(): array
    {
        return [
            'shipping_address' => 'array',
            'delivery_status' => DeliveryStatus::class,
            'payment_status' => PaymentStatus::class,
            'grand_total' => 'decimal:2',
            'coupon_discount' => 'decimal:2',
            'cancel_requested_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(OrderDetail::class);
    }

    /** Same shape as the reference, e.g. 20260721-14411730: timestamp plus two digits. */
    public static function nextCode(): string
    {
        do {
            $code = now()->format('Ymd-His').str_pad((string) random_int(0, 99), 2, '0', STR_PAD_LEFT);
        } while (static::where('code', $code)->exists());

        return $code;
    }

    /** "cash_on_delivery" => "Cash on delivery"; new gateways read sensibly without a lookup table. */
    public function paymentMethodLabel(): string
    {
        return ucfirst(str_replace('_', ' ', (string) $this->payment_type));
    }

    public function subtotal(): float
    {
        return round($this->details->sum(fn (OrderDetail $d) => (float) $d->price * $d->quantity), 2);
    }

    public function taxTotal(): float
    {
        return round($this->details->sum(fn (OrderDetail $d) => (float) $d->tax), 2);
    }

    public function shippingTotal(): float
    {
        return round($this->details->sum(fn (OrderDetail $d) => (float) $d->shipping_cost), 2);
    }

    /**
     * List filters from a query string, shared by the panel and the API.
     *
     * Unknown values are dropped rather than rejected: a hand-edited or stale
     * bookmarked URL should still open the list.
     *
     * @return array{search:string, delivery_status:?DeliveryStatus, payment_status:?PaymentStatus, date_from:?string, date_to:?string, sort:string}
     */
    public static function filtersFrom(array $query): array
    {
        $date = function (mixed $v): ?string {
            if (! is_string($v) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) {
                return null;
            }
            $d = Carbon::createFromFormat('!Y-m-d', $v);

            return $d && $d->format('Y-m-d') === $v ? $v : null; // rejects 2026-02-31
        };
        $sort = $query['sort'] ?? null;

        return [
            'search' => is_string($query['search'] ?? null) ? trim($query['search']) : '',
            'delivery_status' => is_string($query['delivery_status'] ?? null) ? DeliveryStatus::tryFrom($query['delivery_status']) : null,
            'payment_status' => is_string($query['payment_status'] ?? null) ? PaymentStatus::tryFrom($query['payment_status']) : null,
            'date_from' => $date($query['date_from'] ?? null),
            'date_to' => $date($query['date_to'] ?? null),
            'sort' => is_string($sort) && array_key_exists($sort, self::SORTS) ? $sort : 'newest',
        ];
    }

    /** Applies filtersFrom() output, including the sort. */
    public function scopeFiltered(Builder $query, array $f): Builder
    {
        [$column, $direction] = self::SORTS[$f['sort']];

        // whereContains matches typed % and _ literally (emails often contain _).
        $like = fn (Builder $q, array $columns) => $q->whereContains($columns, $f['search']);

        // Dates are whole days in the app timezone (Asia/Dhaka), which is also the
        // timezone created_at is stored in, so no conversion is needed; date_to is inclusive.
        return $query
            ->when($f['search'], fn (Builder $q) => $q->where(fn (Builder $q) => $like($q, ['code', 'shipping_address->phone'])
                ->orWhereHas('customer', fn (Builder $c) => $like($c, ['name', 'email', 'phone']))))
            ->when($f['delivery_status'], fn (Builder $q, DeliveryStatus $s) => $q->where('delivery_status', $s->value))
            ->when($f['payment_status'], fn (Builder $q, PaymentStatus $s) => $q->where('payment_status', $s->value))
            ->when($f['date_from'], fn (Builder $q, string $d) => $q->where('created_at', '>=', Carbon::parse($d)->startOfDay()))
            ->when($f['date_to'], fn (Builder $q, string $d) => $q->where('created_at', '<', Carbon::parse($d)->addDay()->startOfDay()))
            ->orderBy($column, $direction)
            ->orderByDesc('id'); // stable paging when amounts tie
    }
}
