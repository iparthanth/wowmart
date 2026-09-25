<?php

namespace App\Http\Requests\Admin;

use App\Models\Warranty;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

/** The warranty form (inline on the list, and the edit page) and the API's create/replace body. */
class WarrantyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // the route group already requires an admin
    }

    public function rules(): array
    {
        return [
            'text' => ['bail', 'required', 'string', 'max:100', $this->uniqueText(...)],
        ];
    }

    private function uniqueText(string $attribute, mixed $value, Closure $fail): void
    {
        $taken = Warranty::whereRaw('lower(text) = lower(?)', [$value])
            ->when($this->route('warranty'), fn ($q, Warranty $w) => $q->whereKeyNot($w->id))
            ->exists();

        if ($taken) {
            $fail('This warranty already exists. Pick it on the product form instead.');
        }
    }
}
