<?php

namespace App\Http\Requests\Admin;

use App\Models\Country;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

/** The country form (inline on the list, and the edit page) and the API's create/replace body. */
class CountryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // the route group already requires an admin
    }

    protected function prepareForValidation(): void
    {
        // One stored case keeps uniqueness checks and lookups exact.
        $code = $this->input('code');
        $this->merge(['code' => is_string($code) ? strtoupper(trim($code)) : $code]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:60'],
            'code' => ['bail', 'required', 'string', 'regex:/^[A-Z]{2,3}$/', $this->uniqueCode(...)],
            'status' => ['sometimes', 'boolean'],
        ];
    }

    private function uniqueCode(string $attribute, mixed $value, Closure $fail): void
    {
        $taken = Country::where('code', $value)
            ->when($this->route('country'), fn ($q, Country $c) => $q->whereKeyNot($c->id))
            ->exists();

        if ($taken) {
            $fail('Another country already uses this code.');
        }
    }

    public function messages(): array
    {
        return [
            'code.regex' => 'Enter the ISO code as two or three letters, like BD or USA.',
        ];
    }
}
