<?php

namespace App\Http\Requests\Admin;

use App\Enums\DiscountType;
use App\Models\FlashDeal;
use App\Models\Product;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * The flash deal form (create and edit) and the API's create/replace body.
 *
 * Products arrive in two shapes - the form's products[<id>][include|discount|discount_type]
 * and the API's [{id, discount, discount_type}] - and are validated as one:
 * products[<id>] => {discount, discount_type}, included rows only. So per-product
 * errors are keyed by product id (products.<id>.discount) in both, never by list position.
 */
class FlashDealRequest extends FormRequest
{
    /** @var array<int, string>|null product id => unit_price, loaded once per request */
    private ?array $prices = null;

    /** Set while normalising when a product row cannot be read at all. */
    private ?string $productsProblem = null;

    public function authorize(): bool
    {
        return true; // the route group already requires an admin
    }

    protected function prepareForValidation(): void
    {
        // Every field is always written, so an absent one is cleared (unticked box, API PUT).
        $this->merge([
            'status' => $this->boolean('status'),
            'featured' => $this->boolean('featured'),
            'banner' => $this->input('banner') ?: null,
            'background_color' => $this->input('background_color'),
            'text_color' => $this->input('text_color'),
            'products' => $this->normaliseProducts($this->input('products')),
        ]);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            // The columns are MySQL TIMESTAMPs, which only hold 1970 to January 2038.
            'start_date' => ['required', 'date', 'after:2000-01-01'],
            'end_date' => [
                'required', 'date', 'before:2038-01-01',
                // after: parses start_date, and an array there would throw instead of failing.
                Rule::when(is_string($this->input('start_date')), 'after:start_date'),
            ],
            'banner' => ['nullable', 'integer', Rule::exists('uploads', 'id')->where('type', 'image')],
            'background_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'text_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'status' => ['boolean'],
            'featured' => ['boolean'],
            'products' => ['bail', $this->readableRows(...), 'required', 'array', 'min:1', $this->knownProducts(...), $this->notInOtherDeals(...)],
            'products.*.discount_type' => ['required', Rule::enum(DiscountType::class)],
            'products.*.discount' => ['bail', 'required', 'numeric', 'min:0', $this->withinPrice(...)],
        ];
    }

    private function normaliseProducts(mixed $rows): mixed
    {
        if (! is_array($rows)) {
            return $rows; // let the 'array' rule report it
        }

        // The API sends a list; the form keys rows by product id, which is never a list (ids start at 1).
        $api = array_is_list($rows);
        $out = [];
        foreach ($rows as $key => $row) {
            if ($api) {
                $id = is_array($row) ? ($row['id'] ?? null) : null;
            } elseif (is_array($row) && filter_var($row['include'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                $id = $key;
            } else {
                continue; // an unticked form row
            }

            // Only whole numbers: PHP would quietly turn true or 1.7 into product 1.
            if (! is_int($id) && ! (is_string($id) && ctype_digit($id))) {
                $this->productsProblem = 'Each product needs its numeric id.';
            } elseif (isset($out[(int) $id])) {
                $this->productsProblem = 'Each product can appear only once in a deal.';
            } else {
                $out[(int) $id] = ['discount' => $row['discount'] ?? null, 'discount_type' => $row['discount_type'] ?? null];
            }
        }

        return $out;
    }

    private function prices(): array
    {
        return $this->prices ??= Product::whereIn('id', array_keys((array) $this->input('products')))
            ->pluck('unit_price', 'id')
            ->all();
    }

    private function readableRows(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->productsProblem !== null) {
            $fail($this->productsProblem);
        }
    }

    private function knownProducts(string $attribute, mixed $value, Closure $fail): void
    {
        if (array_diff(array_keys($value), array_keys($this->prices())) !== []) {
            $fail('One of the chosen products no longer exists. Reload the page and choose again.');
        }
    }

    /** The product form holds one deal per product, so a second deal would be dropped on its next save. */
    private function notInOtherDeals(string $attribute, mixed $value, Closure $fail): void
    {
        $clash = DB::table('flash_deal_products')
            ->join('products', 'products.id', '=', 'flash_deal_products.product_id')
            ->join('flash_deals', 'flash_deals.id', '=', 'flash_deal_products.flash_deal_id')
            ->whereIn('flash_deal_products.product_id', array_keys($value))
            ->where('flash_deal_products.flash_deal_id', '!=', $this->route('flashDeal')?->id ?? 0)
            ->orderBy('products.name')
            ->first(['products.name', 'flash_deals.title']);

        if ($clash) {
            $fail(sprintf('"%s" is already in the flash deal "%s". Take it out of that deal first.', $clash->name, $clash->title));
        }
    }

    private function withinPrice(string $attribute, mixed $value, Closure $fail): void
    {
        $id = (int) explode('.', $attribute)[1];
        $price = $this->prices()[$id] ?? null;
        $type = $this->input("products.{$id}.discount_type");

        // An unknown product or a bad type is reported by its own rule.
        $problem = $price === null || ! is_string($type) ? null : FlashDeal::discountProblem((float) $value, $type, (float) $price);
        if ($problem) {
            $fail($problem);
        }
    }

    public function attributes(): array
    {
        return ['products.*.discount' => 'discount', 'products.*.discount_type' => 'discount type'];
    }

    public function messages(): array
    {
        return [
            'start_date.after' => 'Choose a start date after 1 January 2000.',
            'end_date.before' => 'Choose an end date before 2038.',
            'end_date.after' => 'The deal must end after it starts.',
            'products.required' => 'Include at least one product in the deal.',
            'products.min' => 'Include at least one product in the deal.',
            'products.*.discount.required' => 'Enter a discount for each included product (0 for none).',
            'products.*.discount.numeric' => 'Enter the discount as a number, such as 10 or 2.50.',
            'products.*.discount.min' => 'A discount cannot be negative.',
            'products.*.discount_type.required' => 'Choose Percent or Amount for each included product.',
            'products.*.discount_type.enum' => 'Choose Percent or Amount for each included product.',
            'background_color.regex' => 'Use a colour like #14213D.',
            'text_color.regex' => 'Use a colour like #FFFFFF.',
            'banner.exists' => 'Choose the banner from the image library.',
        ];
    }
}
