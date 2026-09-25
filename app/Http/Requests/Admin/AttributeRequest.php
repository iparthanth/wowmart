<?php

namespace App\Http\Requests\Admin;

use App\Models\Attribute;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

/** Add or rename an attribute (panel and API). */
class AttributeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // the route group already requires an admin
    }

    public function rules(): array
    {
        return [
            'name' => ['bail', 'required', 'string', 'max:60', $this->uniqueName(...)],
        ];
    }

    /** "Size" and "size" would be two identical choices on the product form. lower() on both sides, as in BrandRequest. */
    private function uniqueName(string $attribute, mixed $value, Closure $fail): void
    {
        $taken = Attribute::whereRaw('lower(name) = lower(?)', [$value])
            ->when($this->route('attribute'), fn ($q, Attribute $a) => $q->whereKeyNot($a->id))
            ->exists();

        if ($taken) {
            $fail('An attribute with this name already exists. Add values to it instead of creating another.');
        }
    }
}
