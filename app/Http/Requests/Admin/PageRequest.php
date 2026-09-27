<?php

namespace App\Http\Requests\Admin;

use App\Models\Page;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

/** The page form (inline on the list, and the edit page) and the API's create/replace body. */
class PageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // the route group already requires an admin
    }

    protected function prepareForValidation(): void
    {
        // A blank slug derives from the title, one stored case keeps links stable.
        if (! $this->filled('slug') && is_string($this->input('title'))) {
            $this->merge(['slug' => Str::slug($this->input('title'))]);
        }

        $this->merge(['slug' => is_string($this->input('slug')) ? Str::slug($this->input('slug')) : $this->input('slug')]);
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'in:'.implode(',', Page::TYPES)],
            'title' => ['required', 'string', 'max:120'],
            'slug' => ['bail', 'required', 'string', 'max:140', $this->uniqueSlug(...)],
            'content' => ['nullable', 'string', 'max:60000'],
            'meta_title' => ['nullable', 'string', 'max:180'],
            'meta_description' => ['nullable', 'string', 'max:300'],
            'status' => ['sometimes', 'boolean'],
        ];
    }

    private function uniqueSlug(string $attribute, mixed $value, Closure $fail): void
    {
        $taken = Page::where('slug', $value)
            ->when($this->route('page'), fn ($q, Page $p) => $q->whereKeyNot($p->id))
            ->exists();

        if ($taken) {
            $fail('Another page already uses this address. Pick a different slug.');
        }
    }
}
