<?php

namespace App\Http\Requests\Admin;

use App\Models\Brand;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** The brand form: inline on the brand list, and the edit page. Also the API's create/replace body. */
class BrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // the route group already requires an admin
    }

    protected function prepareForValidation(): void
    {
        // Every field is always written, so an absent one is cleared rather than kept:
        // an unticked checkbox means "not a top brand", and an API PUT is a full replacement.
        // A sent `top` is left as-is so the boolean rule can refuse junk like "maybe".
        $this->merge([
            'top' => $this->input('top', false),
            'logo' => $this->input('logo'),
            'meta_title' => $this->input('meta_title'),
            'meta_description' => $this->input('meta_description'),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['bail', 'required', 'string', 'max:100', $this->uniqueName(...)],
            'logo' => ['nullable', 'integer', Rule::exists('uploads', 'id')->where('type', 'image')],
            'top' => ['boolean'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * "Arduino" and "arduino" are the same brand to a shopper. A plain unique
     * rule would let both through on SQLite and case-sensitive collations.
     * Both sides go through the database's lower(): SQLite's only folds ASCII,
     * and mixing it with PHP's mb_strtolower let an exact "Élan" twice through.
     */
    private function uniqueName(string $attribute, mixed $value, Closure $fail): void
    {
        $taken = Brand::whereRaw('lower(name) = lower(?)', [$value])
            ->when($this->route('brand'), fn ($q, Brand $brand) => $q->whereKeyNot($brand->id))
            ->exists();

        if ($taken) {
            $fail('A brand with this name already exists. Use a different name, or edit the existing brand.');
        }
    }

    public function messages(): array
    {
        return [
            'logo.exists' => 'Choose the logo from the image library.',
        ];
    }
}
