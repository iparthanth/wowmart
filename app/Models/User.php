<?php

namespace App\Models;

use App\Enums\DeliveryStatus;
use App\Enums\UserType;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Every person who signs in: customers, sellers, staff, delivery and admins.
 *
 * `user_type` and `banned` are deliberately NOT fillable. That omission is the
 * privilege-escalation defence: a crafted `user_type=admin` field in any
 * registration or profile request is silently discarded.
 */
#[Fillable(['name', 'email', 'phone', 'country_code', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'user_type' => UserType::class,
            'banned' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->user_type === UserType::Admin;
    }

    public function shop(): HasOne
    {
        return $this->hasOne(Shop::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /** Customer list sort key => [column, direction]. Anything else falls back to newest. */
    public const CUSTOMER_SORTS = [
        'newest' => ['id', 'desc'],
        'oldest' => ['id', 'asc'],
        'spent' => ['total_spent', 'desc'],
    ];

    public function scopeCustomers(Builder $query): Builder
    {
        return $query->where('user_type', UserType::Customer->value);
    }

    /** Adds total_spent: the sum of grand_total over orders that were not cancelled. */
    public function scopeWithTotalSpent(Builder $query): Builder
    {
        return $query->withSum(
            ['orders as total_spent' => fn (Builder $q) => $q->where('delivery_status', '!=', DeliveryStatus::Cancelled->value)],
            'grand_total',
        );
    }

    /**
     * Customer list filters from a query string, shared by the panel and the API.
     * Unknown values are dropped rather than rejected, so a stale bookmark still opens.
     *
     * @return array{search:string, status:?string, verification:?string, sort:string}
     */
    public static function customerFiltersFrom(array $query): array
    {
        $pick = fn (string $key, array $allowed) => is_string($v = $query[$key] ?? null) && in_array($v, $allowed, true) ? $v : null;

        return [
            'search' => is_string($query['search'] ?? null) ? trim($query['search']) : '',
            'status' => $pick('status', ['active', 'banned']),
            'verification' => $pick('verification', ['verified', 'unverified']),
            'sort' => $pick('sort', array_keys(self::CUSTOMER_SORTS)) ?? 'newest',
        ];
    }

    /** Applies customerFiltersFrom() output, including the sort (which needs withTotalSpent for "spent"). */
    public function scopeCustomerFiltered(Builder $query, array $f): Builder
    {
        [$column, $direction] = self::CUSTOMER_SORTS[$f['sort']];

        return $query
            ->when($f['search'], fn (Builder $q, string $s) => $q->whereContains(['name', 'email', 'phone'], $s))
            ->when($f['status'], fn (Builder $q, string $s) => $q->where('banned', $s === 'banned'))
            ->when($f['verification'] === 'verified', fn (Builder $q) => $q->whereNotNull('email_verified_at'))
            ->when($f['verification'] === 'unverified', fn (Builder $q) => $q->whereNull('email_verified_at'))
            ->orderBy($column, $direction)
            ->orderByDesc('id'); // stable paging when totals tie
    }
}
