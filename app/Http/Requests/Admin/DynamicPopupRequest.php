<?php

namespace App\Http\Requests\Admin;

use App\Models\DynamicPopup;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** The pop-up form (inline on the list, and the edit page) and the API's create/replace body. */
class DynamicPopupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // the route group already requires an admin
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'banner' => ['nullable', 'integer', Rule::exists('uploads', 'id')->where('type', 'image')],
            'link' => ['nullable', 'string', 'max:255'],
            'page' => ['required', 'in:'.implode(',', DynamicPopup::PAGES)],
            'once_per_session' => ['sometimes', 'boolean'],
            'status' => ['sometimes', 'boolean'],
        ];
    }
}
