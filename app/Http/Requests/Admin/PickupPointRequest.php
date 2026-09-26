<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/** The pickup point form (inline on the list, and the edit page) and the API's create/replace body. */
class PickupPointRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // the route group already requires an admin
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:80'],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['required', 'array'],
            'address.address' => ['required', 'string', 'max:300'],
            'address.city' => ['required', 'string', 'max:60'],
            'address.country' => ['required', 'string', 'max:60'],
            'status' => ['sometimes', 'boolean'],
        ];
    }

    /** @return array{name: string, phone: string, address: array{address: string, city: string, country: string}, status?: bool} */
    public function payload(): array
    {
        return $this->validated();
    }
}
