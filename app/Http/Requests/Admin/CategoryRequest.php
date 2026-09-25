<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** The category form, shared by the panel and the API (PUT is a full replacement). */
class CategoryRequest extends FormRequest
{
    private const CHECKBOXES = ['featured', 'digital'];

    private const OPTIONAL = ['parent_id', 'icon', 'banner', 'cover_image', 'meta_title', 'meta_description'];

    public function authorize(): bool
    {
        return true; // the route group already requires an admin
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            // Full replacement: an optional field left out means "none", not "keep".
            ...array_fill_keys(array_diff(self::OPTIONAL, array_keys($this->all())), null),
            // An unticked checkbox is not sent, so missing means false; anything else
            // goes to the boolean rule untouched so junk gets a 422 instead of false.
            ...array_combine(self::CHECKBOXES, array_map(fn ($f) => $this->input($f) ?? false, self::CHECKBOXES)),
            'order_level' => $this->input('order_level') ?? 0,
        ]);
    }

    public function rules(): array
    {
        $category = $this->route('category');
        // A category cannot sit under itself or anything below it; that would cut the branch off the tree.
        $forbidden = $category instanceof Category ? [$category->id, ...$category->descendantIds()] : [];
        $image = Rule::exists('uploads', 'id')->where('type', 'image');

        return [
            'name' => ['required', 'string', 'max:100'],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id', Rule::notIn($forbidden)],
            'order_level' => ['required', 'integer', 'min:0', 'max:9999'],
            'featured' => ['boolean'],
            'digital' => ['boolean'],
            'icon' => ['nullable', 'integer', $image],
            'banner' => ['nullable', 'integer', $image],
            'cover_image' => ['nullable', 'integer', $image],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'parent_id.not_in' => 'A category cannot go inside itself or one of its own subcategories. Choose another parent.',
            'order_level.max' => 'The order level must be between 0 and 9999; lower numbers show first.',
        ];
    }

    public function attributes(): array
    {
        return [
            'parent_id' => 'parent category',
            'cover_image' => 'cover image',
        ];
    }
}
