<?php

namespace App\Services;

use App\Enums\UserType;
use App\Models\Product;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Every write to a customer account, for the panel and the API alike.
 * Input is validated already (CustomerRequest).
 */
class CustomerService
{
    /** email_verified_at stays empty: an admin typing an address does not prove the customer owns it. */
    public function create(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $customer = new User($this->attributes($data));
            // Not fillable, so set here and never from input.
            $customer->forceFill(['user_type' => UserType::Customer])->save();

            return $customer->refresh(); // picks up column defaults such as banned
        });
    }

    /** A null password means "keep the current one". */
    public function update(User $customer, array $data): User
    {
        return DB::transaction(function () use ($customer, $data) {
            $attributes = $this->attributes($data);

            if (($attributes['password'] ?? null) === null) {
                unset($attributes['password']);
            }

            return tap($customer)->update($attributes);
        });
    }

    /** Sets exactly the given flags, so repeating a request changes nothing. */
    public function setFlags(User $customer, array $flags): User
    {
        return DB::transaction(function () use ($customer, $flags) {
            $customer = User::whereKey($customer->id)->lockForUpdate()->firstOrFail();
            // banned is not fillable on purpose; this is the one place it changes.
            $customer->forceFill(array_intersect_key($flags, ['banned' => true]))->save();

            return $customer;
        });
    }

    /**
     * Order history must stay (orders.user_id also restricts deletes at the database).
     * Without orders, the address book, cart, wishlist and reviews go with the
     * account (cascade), so the ratings those reviews fed are recomputed.
     *
     * @throws DomainException when the customer has any order
     */
    public function delete(User $customer): void
    {
        DB::transaction(function () use ($customer) {
            $customer = User::whereKey($customer->id)->lockForUpdate()->firstOrFail();
            $orders = $customer->orders()->count();

            if ($orders > 0) {
                throw new DomainException(sprintf(
                    '%s has %d %s; order history must stay. Ban the account instead.',
                    $customer->name, $orders, Str::plural('order', $orders),
                ));
            }

            $reviewed = $customer->reviews()->pluck('product_id')->unique();

            $customer->tokens()->delete(); // polymorphic, so no foreign key cascades them
            $customer->delete();

            foreach ($reviewed as $productId) {
                $this->refreshRating((int) $productId);
            }
        });
    }

    /** Same rule as the review screens: the average of visible reviews, 0 when there are none. */
    private function refreshRating(int $productId): void
    {
        $product = Product::lockForUpdate()->find($productId);

        // reorder(): MySQL's ONLY_FULL_GROUP_BY rejects an aggregate ordered by id.
        $product?->forceFill(['rating' => round((float) $product->reviews()->where('status', true)->reorder()->avg('rating'), 2)])->save();
    }

    /** A country code only means something next to a phone number (the storefront stores it the same way). */
    private function attributes(array $data): array
    {
        return [...$data, 'country_code' => ($data['phone'] ?? null) === null ? null : $data['country_code']];
    }
}
