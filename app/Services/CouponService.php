<?php

namespace App\Services;

use App\Enums\CouponType;
use App\Models\Coupon;
use App\Models\User;
use DomainException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

/** Every write to a coupon, for the panel and the API alike. Input is validated already (CouponRequest). */
class CouponService
{
    private const FIELDS = ['code', 'discount', 'discount_type', 'start_date', 'end_date', 'status'];

    public function create(array $data, ?User $by = null): Coupon
    {
        return DB::transaction(function () use ($data, $by) {
            $type = CouponType::from($data['type']);
            $coupon = new Coupon($this->fields($data));
            $coupon->forceFill(['type' => $type, 'details' => $this->details($type, $data), 'user_id' => $by?->id])->save();

            return $coupon;
        });
    }

    /** The type is never rewritten: the details are rebuilt for the type the coupon already has. */
    public function update(Coupon $coupon, array $data): Coupon
    {
        return DB::transaction(function () use ($coupon, $data) {
            $coupon->fill($this->fields($data));
            $coupon->forceFill(['details' => $this->details($coupon->type, $data)])->save();

            return $coupon;
        });
    }

    /** Sets exactly the given flags, so repeating a request changes nothing. */
    public function setFlags(Coupon $coupon, array $flags): Coupon
    {
        return DB::transaction(fn () => tap($coupon)->update($flags));
    }

    /**
     * coupon_usages cascade on delete, so removing a used coupon would erase
     * the record of who redeemed it. Refuse instead.
     *
     * @throws DomainException once the coupon has been used
     */
    public function delete(Coupon $coupon): void
    {
        DB::transaction(function () use ($coupon) {
            // The storefront writes usages; locking the parent row makes a new usage
            // wait for us, instead of committing unseen and then vanishing in the cascade.
            Coupon::whereKey($coupon->id)->lockForUpdate()->firstOrFail();
            $count = $coupon->usages()->count();

            if ($count > 0) {
                throw new DomainException(sprintf(
                    'Used %d %s - switch it off instead, so order history keeps its record.',
                    $count, $count === 1 ? 'time' : 'times',
                ));
            }

            $coupon->delete();
        });
    }

    /**
     * The datetime cast writes the wall-clock time and drops any offset, so an
     * API client's "...T00:00:00+00:00" would be stored six hours early in Dhaka.
     */
    private function fields(array $data): array
    {
        $fields = Arr::only($data, self::FIELDS);

        foreach (['start_date', 'end_date'] as $key) {
            $fields[$key] = Date::parse($data[$key])->setTimezone(config('app.timezone'));
        }

        return $fields;
    }

    /** Only the keys the type defines, in the shapes the storefront reads. Never the raw request. */
    private function details(CouponType $type, array $data): array
    {
        // "500" -> 500, "12.5" -> 12.5, so the JSON holds numbers, not strings.
        $num = fn ($v) => $v === null || $v === '' ? null : 0 + $v;

        return match ($type) {
            CouponType::ProductBase => ['product_ids' => array_map('intval', array_values($data['product_ids']))],
            CouponType::CartBase => ['min_buy' => $num($data['min_buy']), 'max_discount' => $num($data['max_discount'] ?? null)],
            CouponType::WelcomeBase => ['validation_days' => (int) $data['validation_days'], 'min_buy' => $num($data['min_buy'] ?? null) ?? 0],
        };
    }
}
