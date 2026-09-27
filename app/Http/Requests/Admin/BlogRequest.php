<?php

namespace App\Http\Requests\Admin;

use App\Models\Blog;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

/** The blog post form (inline on the list, and the edit page) and the API's create/replace body. */
class BlogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // the route group already requires an admin
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('slug') && is_string($this->input('title'))) {
            $this->merge(['slug' => Str::slug($this->input('title'))]);
        }

        $this->merge(['slug' => is_string($this->input('slug')) ? Str::slug($this->input('slug')) : $this->input('slug')]);
    }

    public function rules(): array
    {
        return [
            'blog_category_id' => ['required', 'exists:blog_categories,id'],
            'title' => ['required', 'string', 'max:160'],
            'slug' => ['bail', 'required', 'string', 'max:180', $this->uniqueSlug(...)],
            'short_description' => ['nullable', 'string', 'max:300'],
            'description' => ['nullable', 'string', 'max:60000'],
            'banner' => ['nullable', 'string', 'max:255'],
            'meta_title' => ['nullable', 'string', 'max:180'],
            'meta_description' => ['nullable', 'string', 'max:300'],
            'status' => ['sometimes', 'boolean'],
        ];
    }

    private function uniqueSlug(string $attribute, mixed $value, Closure $fail): void
    {
        $taken = Blog::where('slug', $value)
            ->when($this->route('blog'), fn ($q, Blog $b) => $q->whereKeyNot($b->id))
            ->exists();

        if ($taken) {
            $fail('Another post already uses this address. Pick a different slug.');
        }
    }
}
