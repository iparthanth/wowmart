<?php

namespace App\Http\Requests\Admin;

use App\Models\Carrier;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

/** The carrier form (inline on the list, and the edit page) and the API's create/replace body. */
class CarrierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // the route group already requires an admin
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:60', $this->uniqueName(...)],
            'transit_time' => ['nullable', 'string', 'max:60'],
            'free_shipping' => ['sometimes', 'boolean'],
            'status' => ['sometimes', 'boolean'],
        ];
    }

    private function uniqueName(string $attribute, mixed $value, Closure $fail): void
    {
        $taken = Carrier::where('name', $value)
            ->when($this->route('carrier'), fn ($q, Carrier $c) => $q->whereKeyNot($c->id))
            ->exists();

        if ($taken) {
            $fail('Another carrier already uses this name. Edit that carrier instead.');
        }
    }
}
