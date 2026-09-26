<?php

namespace App\Http\Requests\Admin;

use App\Models\Tax;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

/** The tax form (inline on the list, and the edit page) and the API's create/replace body. */
class TaxRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // the route group already requires an admin
    }

    public function rules(): array
    {
        return [
            'name' => ['bail', 'required', 'string', 'max:60', $this->uniqueName(...)],
            'status' => ['sometimes', 'boolean'],
        ];
    }

    private function uniqueName(string $attribute, mixed $value, Closure $fail): void
    {
        $taken = Tax::where('name', $value)
            ->when($this->route('tax'), fn ($q, Tax $t) => $q->whereKeyNot($t->id))
            ->exists();

        if ($taken) {
            $fail('A tax with this name already exists. Edit that tax instead.');
        }
    }
}
