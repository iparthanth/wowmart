<?php

namespace App\Http\Requests\Admin;

use App\Models\BlogCategory;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

/** The blog category form (inline on the list, and the edit page) and the API's create/replace body. */
class BlogCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // the route group already requires an admin
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('slug') && is_string($this->input('name'))) {
            $this->merge(['slug' => Str::slug($this->input('name'))]);
        }

        $this->merge(['slug' => is_string($this->input('slug')) ? Str::slug($this->input('slug')) : $this->input('slug')]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:60'],
            'slug' => ['bail', 'required', 'string', 'max:80', $this->uniqueSlug(...)],
            'status' => ['sometimes', 'boolean'],
        ];
    }

    private function uniqueSlug(string $attribute, mixed $value, Closure $fail): void
    {
        $taken = BlogCategory::where('slug', $value)
            ->when($this->route('blog_category'), fn ($q, BlogCategory $c) => $q->whereKeyNot($c->id))
            ->exists();

        if ($taken) {
            $fail('Another category already uses this address. Pick a different slug.');
        }
    }
}
