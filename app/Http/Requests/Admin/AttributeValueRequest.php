<?php

namespace App\Http\Requests\Admin;

use App\Models\AttributeValue;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

/** Add a value to an attribute, or rename one (panel and API). */
class AttributeValueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // the route group already requires an admin
    }

    protected function prepareForValidation(): void
    {
        // The edit page has one rename form per value; a bag per value puts the
        // error (and the retyped text) back on the row that failed, not on every row.
        if ($value = $this->route('value')) {
            $this->errorBag = 'value'.$value->id;
        }
    }

    public function rules(): array
    {
        return [
            'value' => ['bail', 'required', 'string', 'max:100', $this->uniqueWithinAttribute(...)],
        ];
    }

    /** "Large" twice under Size is a duplicate; "Large" under Size and under Pack is not. */
    private function uniqueWithinAttribute(string $attribute, mixed $value, Closure $fail): void
    {
        $current = $this->route('value');
        $attributeId = $current?->attribute_id ?? $this->route('attribute')->id;

        $taken = AttributeValue::where('attribute_id', $attributeId)
            ->whereRaw('lower(value) = lower(?)', [$value])
            ->when($current, fn ($q, AttributeValue $v) => $q->whereKeyNot($v->id))
            ->exists();

        if ($taken) {
            $fail('This attribute already has that value.');
        }
    }
}
