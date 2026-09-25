<?php

namespace App\Http\Requests\Admin;

use App\Enums\CouponType;
use App\Enums\DiscountType;
use App\Models\Coupon;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** The coupon form (create and edit) and the API's create/replace body. */
class CouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // the route group already requires an admin
    }

    protected function prepareForValidation(): void
    {
        $coupon = $this->route('coupon');
        $code = $this->input('code');

        $this->merge([
            // Shoppers type codes in any case; storing one canonical form keeps lookups exact.
            'code' => is_string($code) ? mb_strtoupper(trim($code)) : $code,
            'status' => $this->boolean('status'),
            // The edit form shows the type read-only and does not post it.
            'type' => $this->input('type', $coupon?->type->value),
        ]);
    }

    public function rules(): array
    {
        $coupon = $this->route('coupon');
        $percent = $this->input('discount_type') === DiscountType::Percent->value;
        $type = $this->input('type');

        return [
            // Changing the type would orphan the details written for the old one.
            'type' => ['required', Rule::enum(CouponType::class), ...($coupon ? [Rule::in([$coupon->type->value])] : [])],
            'code' => ['bail', 'required', 'string', 'max:32', 'regex:/^[A-Z0-9-]+$/', $this->uniqueCode(...)],
            'discount_type' => ['required', Rule::enum(DiscountType::class)],
            // decimal:0,2 so 0.001 cannot pass as "> 0" and then be stored as 0.00.
            'discount' => ['required', 'numeric', 'decimal:0,2', 'gt:0', $percent ? 'max:100' : 'max:99999999'],
            // The columns are TIMESTAMP, which MySQL cannot store outside 1970-2038.
            'start_date' => ['required', 'date', 'after:1970-01-02', 'before:2038-01-19'],
            // after: parses start_date, and an array there would throw instead of failing.
            'end_date' => ['required', 'date', Rule::when(is_string($this->input('start_date')), 'after:start_date'), 'before:2038-01-19'],
            'status' => ['boolean'],
            ...$this->detailRules(is_string($type) ? CouponType::tryFrom($type) : null),
        ];
    }

    private function detailRules(?CouponType $type): array
    {
        return match ($type) {
            CouponType::ProductBase => [
                'product_ids' => ['required', 'array', 'min:1'],
                'product_ids.*' => ['integer', 'distinct', 'exists:products,id'],
            ],
            CouponType::CartBase => [
                'min_buy' => ['required', 'numeric', 'min:0', 'max:99999999'],
                'max_discount' => ['nullable', 'numeric', 'gt:0', 'max:99999999'],
            ],
            CouponType::WelcomeBase => [
                'validation_days' => ['required', 'integer', 'between:1,365'],
                'min_buy' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            ],
            null => [],
        };
    }

    /** Codes are stored uppercase, but rows written elsewhere may not be. */
    private function uniqueCode(string $attribute, mixed $value, Closure $fail): void
    {
        $taken = Coupon::whereRaw('upper(code) = ?', [$value])
            ->when($this->route('coupon'), fn ($q, Coupon $coupon) => $q->whereKeyNot($coupon->id))
            ->exists();

        if ($taken) {
            $fail('Another coupon already uses this code. Pick a different code.');
        }
    }

    public function messages(): array
    {
        return [
            'type.in' => 'The type cannot change once a coupon exists. Create a new coupon instead.',
            'code.regex' => 'Use only letters, digits and dashes - no spaces.',
            'discount.max' => $this->input('discount_type') === DiscountType::Percent->value
                ? 'A percentage discount cannot be more than 100.'
                : 'This discount is too large.',
            'discount.decimal' => 'Use at most 2 decimal places.',
            'start_date.after' => 'Pick a date after 1 Jan 1970.',
            'start_date.before' => 'Pick a date before 19 Jan 2038.',
            'end_date.after' => 'The coupon must end after it starts.',
            'end_date.before' => 'Pick a date before 19 Jan 2038.',
            'product_ids.required' => 'Choose at least one product the discount applies to.',
            'product_ids.min' => 'Choose at least one product the discount applies to.',
            'product_ids.*.exists' => 'One of the chosen products no longer exists. Reload the page and choose again.',
        ];
    }
}
