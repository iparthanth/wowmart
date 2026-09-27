<?php

namespace App\Http\Requests\Admin;

use App\Models\CustomAlert;
use Illuminate\Foundation\Http\FormRequest;

/** The alert form (inline on the list, and the edit page) and the API's create/replace body. */
class CustomAlertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // the route group already requires an admin
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:2000'],
            'style' => ['required', 'in:'.implode(',', CustomAlert::STYLES)],
            'status' => ['sometimes', 'boolean'],
        ];
    }
}
