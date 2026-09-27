<?php

namespace App\Http\Requests\Admin;

use App\Models\Newsletter;
use Illuminate\Foundation\Http\FormRequest;

/** The newsletter form (compose) and the API's create/replace body. */
class NewsletterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // the route group already requires an admin
    }

    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:180'],
            'body' => ['required', 'string', 'max:60000'],
            'segment' => ['required', 'in:'.implode(',', Newsletter::SEGMENTS)],
        ];
    }
}
