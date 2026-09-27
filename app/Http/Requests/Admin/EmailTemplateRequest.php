<?php

namespace App\Http\Requests\Admin;

use App\Models\EmailTemplate;
use Illuminate\Foundation\Http\FormRequest;

/** The email template form (edit page) and the API's create/replace body. */
class EmailTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // the route group already requires an admin
    }

    public function rules(): array
    {
        return [
            'receiver' => ['required', 'in:'.implode(',', EmailTemplate::RECEIVERS)],
            'identifier' => ['required', 'string', 'max:60', 'regex:/^[a-z0-9_]+$/'],
            'subject' => ['required', 'string', 'max:180'],
            'body' => ['required', 'string', 'max:60000'],
            'status' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'identifier.regex' => 'Use lowercase letters, digits and underscores, like order_placed.',
        ];
    }
}
