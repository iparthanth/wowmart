<?php

namespace App\Http\Requests\Admin;

use App\Models\Currency;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

/** The currency form (inline on the list, and the edit page) and the API's create/replace body. */
class CurrencyRequest extends FormRequest
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
            'symbol' => ['required', 'string', 'max:8'],
            'code' => ['bail', 'required', 'string', 'regex:/^[A-Z]{3}$/', $this->uniqueCode(...)],
            'exchange_rate' => ['required', 'numeric', 'gt:0', 'max:999999999'],
            'status' => ['sometimes', 'boolean'],
        ];
    }

    private function uniqueCode(string $attribute, mixed $value, Closure $fail): void
    {
        $taken = Currency::where('code', $value)
            ->when($this->route('currency'), fn ($q, Currency $c) => $q->whereKeyNot($c->id))
            ->exists();

        if ($taken) {
            $fail('Another currency already uses this code. Edit that currency instead.');
        }
    }

    public function messages(): array
    {
        return [
            'code.regex' => 'Enter the ISO code as exactly three letters, like USD.',
            'exchange_rate.gt' => 'The exchange rate must be greater than zero.',
        ];
    }
}
