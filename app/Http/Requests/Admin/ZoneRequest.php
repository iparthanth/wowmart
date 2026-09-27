<?php

namespace App\Http\Requests\Admin;

use App\Models\Zone;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

/** The zone form (inline on the list, and the edit page) and the API's create/replace body. */
class ZoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // the route group already requires an admin
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:60', $this->uniqueName(...)],
            'countries' => ['required', 'array', 'min:1'],
            'countries.*' => ['integer', 'exists:countries,id'],
            'status' => ['sometimes', 'boolean'],
        ];
    }

    private function uniqueName(string $attribute, mixed $value, Closure $fail): void
    {
        $taken = Zone::where('name', $value)
            ->when($this->route('zone'), fn ($q, Zone $z) => $q->whereKeyNot($z->id))
            ->exists();

        if ($taken) {
            $fail('Another zone already uses this name. Edit that zone instead.');
        }
    }

    /** The validated row plus the country ids, which zones sync on save. */
    public function payload(): array
    {
        return [$this->validated(), array_map('intval', $this->validated('countries'))];
    }
}
