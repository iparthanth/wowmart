<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/** Refund configuration, shared by the panel and the API (PUT). */
class RefundSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // the route group already requires an admin
    }

    public function rules(): array
    {
        return [
            'refund_request_time' => ['required', 'integer', 'min:1', 'max:365'],
        ];
    }

    public function messages(): array
    {
        return [
            'refund_request_time.integer' => 'Enter a whole number of days, from 1 to 365.',
            'refund_request_time.min' => 'Enter a whole number of days, from 1 to 365.',
            'refund_request_time.max' => 'Enter a whole number of days, from 1 to 365.',
        ];
    }

    public function attributes(): array
    {
        return ['refund_request_time' => 'refund window'];
    }
}
