<?php

namespace App\Http\Requests\Admin;

use App\Models\Language;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** The language form (inline on the list, and the edit page) and the API's create/replace body. */
class LanguageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // the route group already requires an admin
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:60'],
            'code' => [
                'required', 'string', 'max:12', 'regex:/^[a-z][a-z0-9_-]*$/i',
                Rule::unique('languages', 'code')->ignore($this->route('language')),
            ],
            'rtl' => ['sometimes', 'boolean'],
            'status' => ['sometimes', 'boolean'],
        ];
    }

    /** @return array{name: string, code: string, rtl: bool, status: bool} */
    public function payload(): array
    {
        return [
            'name' => $this->validated('name'),
            'code' => strtolower($this->validated('code')),
            'rtl' => $this->boolean('rtl'),
            'status' => $this->boolean('status', true),
        ];
    }

    public function messages(): array
    {
        return [
            'code.regex' => 'Use a short language code like en, bn or ar.',
        ];
    }
}
