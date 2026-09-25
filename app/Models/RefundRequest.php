<?php

namespace App\Models;

use App\Enums\RefundStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A customer's request to get money back for one order line.
 *
 * `refund_status` is not fillable: it only moves through RefundService::decide().
 */
#[Fillable(['order_id', 'order_detail_id', 'user_id', 'seller_id', 'refund_amount', 'reason'])]
class RefundRequest extends Model
{
    protected function casts(): array
    {
        return [
            'order_id' => 'integer',
            'order_detail_id' => 'integer',
            'user_id' => 'integer',
            'seller_id' => 'integer',
            'refund_amount' => 'decimal:2',
            'refund_status' => RefundStatus::class,
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderDetail(): BelongsTo
    {
        return $this->belongsTo(OrderDetail::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** null = an in-house line */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * List filters from a query string, shared by the panel and the API.
     * Unknown values are dropped, not rejected, so a stale URL still opens the list.
     *
     * @return array{status: ?RefundStatus, search: string}
     */
    public static function filtersFrom(array $query): array
    {
        return [
            'status' => is_string($query['status'] ?? null) ? RefundStatus::tryFrom($query['status']) : null,
            'search' => is_string($query['search'] ?? null) ? trim($query['search']) : '',
        ];
    }

    /** Search matches the order code or the customer's name. Newest first. */
    public function scopeFiltered(Builder $query, array $f): Builder
    {
        return $query
            ->when($f['status'], fn (Builder $q, RefundStatus $s) => $q->where('refund_status', $s->value))
            ->when($f['search'] !== '', fn (Builder $q) => $q->where(fn (Builder $q) => $q
                ->whereHas('order', fn (Builder $o) => $o->whereContains('code', $f['search']))
                ->orWhereHas('customer', fn (Builder $c) => $c->whereContains('name', $f['search']))))
            ->latest('id');
    }
}
