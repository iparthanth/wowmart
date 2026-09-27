<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * The carrier billing forms: a range (billing window) and a zone price inside
 * one. Both are small add forms on the carrier's edit page and the API.
 */
class CarrierRangeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // the route group already requires an admin
    }

    /** @return array<string, mixed> rules for the kind of write this request carries */
    public function rules(): array
    {
        // A range add carries billing_type/lower/upper; a price add carries zone_id/price.
        return $this->has('zone_id')
            ? [
                'zone_id' => ['required', 'exists:zones,id'],
                'price' => ['required', 'numeric', 'min:0', 'max:999999'],
            ]
            : [
                'billing_type' => ['required', 'in:weight,price'],
                'lower' => ['required', 'numeric', 'min:0', 'max:999999'],
                'upper' => ['nullable', 'numeric', 'gt:lower', 'max:999999'],
            ];
    }
}
