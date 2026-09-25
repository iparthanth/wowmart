<?php

namespace App\Http\Requests\Admin;

use App\Enums\DiscountType;
use App\Enums\ShippingType;
use App\Enums\StockVisibility;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * The seven-section product form. Field names match the captured reference form
 * (docs/reference/product-form.md) except where noted, so the request-to-column
 * mapping stays one-to-one and auditable.
 *
 * Differences from the reference, all in the direction of less parsing:
 *  - the discount window is two native datetime inputs, not one "a to b" string
 *  - tags are one comma-separated input, not a tokeniser widget
 *  - gallery photos arrive as photos[] ids, not a CSV string
 */
class ProductRequest extends FormRequest
{
    /** Unticked checkboxes are simply absent from a form post, so each is normalised to a real boolean. */
    private const CHECKBOXES = ['refundable', 'featured', 'todays_deal', 'cash_on_delivery', 'is_quantity_multiplied', 'has_warranty'];

    public function authorize(): bool
    {
        return true; // the route group already requires an admin
    }

    protected function prepareForValidation(): void
    {
        $tags = collect(explode(',', (string) $this->input('tags')))
            ->map(fn ($t) => trim($t))
            ->filter()
            ->unique(fn ($t) => mb_strtolower($t))
            ->implode(',');

        $this->merge([
            ...array_combine(self::CHECKBOXES, array_map(fn ($f) => $this->boolean($f), self::CHECKBOXES)),
            'tags' => $tags === '' ? null : $tags,
            'frequently_bought_selection_type' => $this->filled('fq_bought_product_category_id') ? 'category' : 'product',
        ]);
    }

    public function rules(): array
    {
        $percent = $this->input('discount_type') === DiscountType::Percent->value;

        return [
            // General
            'name' => ['required', 'string', 'max:200'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'brand_id' => ['nullable', 'integer', 'exists:brands,id'],
            'unit' => ['nullable', 'string', 'max:32'],
            'weight' => ['nullable', 'numeric', 'min:0', 'max:99999'],
            'min_qty' => ['required', 'integer', 'min:1', 'max:100000'],
            'tags' => ['nullable', 'string', 'max:1000'],
            'barcode' => ['nullable', 'string', 'max:64'],
            'quick_overview' => ['nullable', 'string', 'max:2000'],
            'description' => ['nullable', 'string', 'max:65000'],
            'refundable' => ['boolean'],
            'refund_note_id' => ['nullable', 'integer', Rule::exists('notes', 'id')->where('type', 'refund')],
            'featured' => ['boolean'],
            'todays_deal' => ['boolean'],
            'flash_deal_id' => ['nullable', 'integer', 'exists:flash_deals,id'],
            'flash_discount' => ['nullable', 'required_with:flash_deal_id', 'numeric', 'min:0'],
            'flash_discount_type' => ['nullable', 'required_with:flash_deal_id', Rule::enum(DiscountType::class)],

            // Files & media - ids from the media library, never raw files
            'photos' => ['nullable', 'array', 'max:20'],
            'photos.*' => ['integer', Rule::exists('uploads', 'id')->where('type', 'image')],
            'thumbnail_img' => ['nullable', 'integer', Rule::exists('uploads', 'id')->where('type', 'image')],
            'video_provider' => ['nullable', Rule::in(['youtube', 'vimeo'])],
            'video_link' => ['nullable', 'required_with:video_provider', 'url:https', 'max:255'],
            'pdf' => ['nullable', 'integer', Rule::exists('uploads', 'id')->where('type', 'document')],

            // Price & stock
            'unit_price' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'discount_type' => ['required', Rule::enum(DiscountType::class)],
            // A percentage caps at 100; a flat amount can never exceed the price itself.
            'discount' => ['nullable', 'numeric', 'min:0', $percent ? 'max:100' : 'lte:unit_price'],
            'discount_start_date' => ['nullable', 'date'],
            'discount_end_date' => ['nullable', 'date', 'after:discount_start_date'],
            'current_stock' => ['required', 'integer', 'min:0', 'max:10000000'],
            'sku' => ['nullable', 'string', 'max:64'],
            'low_stock_quantity' => ['required', 'integer', 'min:0', 'max:100000'],
            'stock_visibility_state' => ['required', Rule::enum(StockVisibility::class)],
            'external_link' => ['nullable', 'url:http,https', 'max:255'],
            'external_link_btn' => ['nullable', 'required_with:external_link', 'string', 'max:60'],

            // SEO
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
            'meta_img' => ['nullable', 'integer', Rule::exists('uploads', 'id')->where('type', 'image')],

            // Shipping
            'cash_on_delivery' => ['boolean'],
            'shipping_type' => ['required', Rule::enum(ShippingType::class)],
            'shipping_cost' => ['nullable', 'required_if:shipping_type,'.ShippingType::FlatRate->value, 'numeric', 'min:0', 'max:1000000'],
            'is_quantity_multiplied' => ['boolean'],
            'est_shipping_days' => ['nullable', 'integer', 'min:1', 'max:365'],

            // Warranty
            'has_warranty' => ['boolean'],
            'warranty_id' => ['nullable', Rule::requiredIf(fn () => $this->boolean('has_warranty')), 'integer', 'exists:warranties,id'],
            'warranty_note_id' => ['nullable', 'integer', Rule::exists('notes', 'id')->where('type', 'warranty')],

            // Frequently bought
            'frequently_bought_selection_type' => ['required', Rule::in(['product', 'category'])],
            'fq_bought_product_category_id' => ['nullable', 'integer', 'exists:categories,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'discount.lte' => 'A flat discount cannot be more than the price.',
            'discount.max' => 'A percentage discount cannot be more than 100.',
            'discount_end_date.after' => 'The discount has to end after it starts.',
            'shipping_cost.required_if' => 'Enter the flat rate, or choose free shipping.',
            'warranty_id.required' => 'Choose a warranty period, or untick "This product has a warranty".',
            'video_link.url' => 'The video link must be a full https:// address.',
        ];
    }

    public function attributes(): array
    {
        return [
            'category_id' => 'main category',
            'unit_price' => 'price',
            'current_stock' => 'stock',
            'low_stock_quantity' => 'low-stock warning level',
            'discount_end_date' => 'discount end',
            'photos.*' => 'gallery image',
            'thumbnail_img' => 'thumbnail',
            'warranty_id' => 'warranty period',
            'external_link_btn' => 'button text',
        ];
    }
}
