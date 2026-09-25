<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Customers write reviews in the storefront; the admin only moderates them.
 * `status` (visible on the storefront) is therefore not fillable - it changes
 * only through App\Services\ReviewService::setFlags.
 */
#[Fillable(['product_id', 'user_id', 'rating', 'comment'])]
class Review extends Model
{
    /** Sort key => [column, direction]. Anything else falls back to newest. */
    public const SORTS = [
        'newest' => ['id', 'desc'],
        'oldest' => ['id', 'asc'],
        'lowest' => ['rating', 'asc'],
        'highest' => ['rating', 'desc'],
    ];

    protected function casts(): array
    {
        return [
            'product_id' => 'integer',
            'user_id' => 'integer',
            'rating' => 'integer',
            'status' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * List filters from a query string, shared by the panel and the API.
     * Unknown values are dropped rather than rejected, so a stale bookmark still opens.
     *
     * @return array{search:string, rating:?int, status:?string, sort:string}
     */
    public static function filtersFrom(array $query): array
    {
        $rating = $query['rating'] ?? null;
        $status = $query['status'] ?? null;
        $sort = $query['sort'] ?? null;

        return [
            'search' => is_string($query['search'] ?? null) ? trim($query['search']) : '',
            'rating' => is_string($rating) && in_array($rating, ['1', '2', '3', '4', '5'], true) ? (int) $rating : null,
            'status' => is_string($status) && in_array($status, ['visible', 'hidden'], true) ? $status : null,
            'sort' => is_string($sort) && array_key_exists($sort, self::SORTS) ? $sort : 'newest',
        ];
    }

    /** Applies filtersFrom() output, including the sort. */
    public function scopeFiltered(Builder $query, array $f): Builder
    {
        [$column, $direction] = self::SORTS[$f['sort']];

        return $query
            ->when($f['search'], fn (Builder $q, string $s) => $q->where(fn (Builder $q) => $q
                ->whereHas('product', fn (Builder $p) => $p->whereContains('name', $s))
                ->orWhereHas('user', fn (Builder $u) => $u->whereContains('name', $s))))
            ->when($f['rating'], fn (Builder $q, int $r) => $q->where('rating', $r))
            ->when($f['status'], fn (Builder $q, string $s) => $q->where('status', $s === 'visible'))
            ->orderBy($column, $direction)
            ->orderByDesc('id'); // stable paging when ratings tie
    }
}
