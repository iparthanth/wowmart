<?php

namespace App\Services;

use App\Enums\UserType;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\RefundRequest;
use App\Models\Review;
use App\Models\Shop;
use App\Models\User;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Sellers for the panel and the API: the list query both share, and every write.
 *
 * A seller is a users row (user_type = seller) that owns exactly one shop.
 * Verification lives on the shop; the ban flag lives on the user.
 */
class SellerService
{
    /**
     * List filters from a query string. Unknown values are dropped rather than
     * rejected, so a stale or hand-edited URL still opens the list.
     *
     * @return array{search:string, verification:?string, status:?string}
     */
    public static function filtersFrom(array $query): array
    {
        $pick = fn (string $key, array $allowed) => in_array($query[$key] ?? null, $allowed, true) ? $query[$key] : null;

        return [
            'search' => is_string($query['search'] ?? null) ? trim($query['search']) : '',
            'verification' => $pick('verification', Shop::VERIFICATION_STATUSES),
            'status' => $pick('status', ['active', 'banned']),
        ];
    }

    /** Sellers with their shop and both counts, filtered, newest first. */
    public function query(array $f): Builder
    {
        return User::query()
            ->where('user_type', UserType::Seller)
            ->with('shop.logoFile')
            ->addSelect([ // users.* is kept: addSelect adds it before the first subquery
                'products_count' => Product::selectRaw('count(*)')->whereColumn('user_id', 'users.id'),
                'order_lines_count' => OrderDetail::selectRaw('count(*)')->whereColumn('seller_id', 'users.id'),
            ])
            ->when($f['search'], fn (Builder $q, string $s) => $q->where(fn (Builder $q) => $q
                ->whereContains(['name', 'email'], $s)
                ->orWhereHas('shop', fn (Builder $shop) => $shop->whereContains('name', $s))))
            ->when($f['verification'], fn (Builder $q, string $v) => $q->whereHas('shop', fn (Builder $shop) => $shop->where('verification_status', $v)))
            ->when($f['status'], fn (Builder $q, string $s) => $q->where('banned', $s === 'banned'))
            ->orderByDesc('users.id');
    }

    /** One seller with the same counts the list shows. */
    public function find(User $seller): User
    {
        return $this->query(self::filtersFrom([]))->whereKey($seller->id)->firstOrFail();
    }

    /**
     * Approve or reject a shop. Any status may move to approved or rejected, so
     * an admin can re-review a shop later; setting the current status again
     * changes nothing.
     *
     * @throws DomainException when the seller has no shop to verify
     */
    public function setVerification(User $seller, string $status): Shop
    {
        return DB::transaction(function () use ($seller, $status) {
            $shop = Shop::where('user_id', $seller->id)->lockForUpdate()->first()
                ?? throw new DomainException("{$seller->name} has no shop to verify; the seller must set one up first.");

            if ($shop->verification_status !== $status) {
                $shop->forceFill(['verification_status' => $status])->save();
            }

            return $shop;
        });
    }

    /**
     * Sets exactly the given flags, so repeating a request changes nothing.
     *
     * Only the flag is stored here. Hiding a banned seller's products is the
     * storefront's job: its Product::live() scope must also exclude products
     * whose owner is banned, or they stay on sale.
     */
    public function setFlags(User $seller, array $flags): User
    {
        return DB::transaction(function () use ($seller, $flags) {
            $seller->forceFill(['banned' => (bool) $flags['banned']])->save();

            return $seller;
        });
    }

    /**
     * Deleting the user cascades to the shop, the seller's products, and the
     * orders, reviews and coupon uses they made as a customer; order lines and
     * refunds would lose their seller. All of that is sales history, so the
     * delete is refused while any of it exists: ban the seller instead.
     * Uploaded files survive with a blank uploader, which loses nothing.
     *
     * @throws DomainException while the seller has products or any sales history
     */
    public function delete(User $seller): void
    {
        DB::transaction(function () use ($seller) {
            $seller = User::whereKey($seller->id)->lockForUpdate()->firstOrFail();
            $name = $seller->shop?->name ?? $seller->name;

            $products = Product::where('user_id', $seller->id)->count();
            $lines = OrderDetail::where('seller_id', $seller->id)->count();

            if ($products > 0 || $lines > 0) {
                throw new DomainException(sprintf(
                    '%s has %d %s and %d order %s; ban the seller instead.',
                    $name, $products, Str::plural('product', $products), $lines, Str::plural('line', $lines),
                ));
            }

            $history = Order::where('seller_id', $seller->id)->orWhere('user_id', $seller->id)->exists()
                || RefundRequest::where('seller_id', $seller->id)->orWhere('user_id', $seller->id)->exists()
                || Review::where('user_id', $seller->id)->exists()
                || Coupon::where('user_id', $seller->id)->exists();

            if ($history) {
                throw new DomainException("{$name} has orders, refunds, reviews or coupons on record; ban the seller instead.");
            }

            $seller->tokens()->delete(); // polymorphic, so no foreign key removes them
            $seller->delete();           // the shop cascades
        });
    }
}
