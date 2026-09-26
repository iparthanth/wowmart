<?php

namespace App\Http\Requests\Admin;

use App\Models\ProductTax;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** The "charge this product under this tax" form, on the Vat & Tax page. */
class ProductTaxRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // the route group already requires an admin
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', Rule::exists('products', 'id')],
            'tax_id' => ['required', 'integer', Rule::exists('taxes', 'id')],
            'tax' => ['required', 'numeric', 'min:0', 'max:999999'],
            'tax_type' => ['required', 'string', Rule::in(ProductTax::TYPES)],
        ];
    }
}
