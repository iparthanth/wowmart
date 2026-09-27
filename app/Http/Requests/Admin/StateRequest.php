<?php

namespace App\Http\Requests\Admin;

use App\Models\State;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

/** The state form (inline on the list, and the edit page) and the API's create/replace body. */
class StateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // the route group already requires an admin
    }

    public function rules(): array
    {
        return [
            'country_id' => ['required', 'exists:countries,id'],
            'name' => ['required', 'string', 'max:60', $this->uniqueInCountry(...)],
            'status' => ['sometimes', 'boolean'],
        ];
    }

    private function uniqueInCountry(string $attribute, mixed $value, Closure $fail): void
    {
        $countryId = $this->integer('country_id');

        if ($countryId === 0) {
            return; // "exists" already reports the missing country
        }

        $taken = State::where('country_id', $countryId)
            ->where('name', $value)
            ->when($this->route('state'), fn ($q, State $s) => $q->whereKeyNot($s->id))
            ->exists();

        if ($taken) {
            $fail('This country already has a state with this name. Edit that state instead.');
        }
    }
}
