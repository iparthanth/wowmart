<?php

namespace App\Http\Requests\Admin;

use App\Models\Color;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

/** The colour form (inline on the list, and the edit page) and the API's create/replace body. */
class ColorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // the route group already requires an admin
    }

    protected function prepareForValidation(): void
    {
        // Browsers send lowercase from <input type="color">; one stored case keeps
        // products.colors lookups exact.
        $code = $this->input('code');
        $this->merge(['code' => is_string($code) ? strtoupper($code) : $code]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:60'],
            'code' => ['bail', 'required', 'string', 'regex:/^#[0-9A-F]{6}$/', $this->uniqueCode(...)],
        ];
    }

    private function uniqueCode(string $attribute, mixed $value, Closure $fail): void
    {
        $taken = Color::whereRaw('upper(code) = ?', [$value])
            ->when($this->route('color'), fn ($q, Color $c) => $q->whereKeyNot($c->id))
            ->exists();

        if ($taken) {
            $fail('Another colour already uses this code. Edit that colour instead.');
        }
    }

    public function messages(): array
    {
        return [
            'code.regex' => 'Enter the code as # and six hex digits, like #1E5AA8.',
        ];
    }
}
