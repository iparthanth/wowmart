<?php

namespace App\Http\Requests\Admin;

use App\Models\City;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

/** The city form (inline on the list, and the edit page) and the API's create/replace body. */
class CityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // the route group already requires an admin
    }

    public function rules(): array
    {
        return [
            'state_id' => ['required', 'exists:states,id'],
            'name' => ['required', 'string', 'max:60', $this->uniqueInState(...)],
            'cost' => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'status' => ['sometimes', 'boolean'],
        ];
    }

    private function uniqueInState(string $attribute, mixed $value, Closure $fail): void
    {
        $stateId = $this->integer('state_id');

        if ($stateId === 0) {
            return; // "exists" already reports the missing state
        }

        $taken = City::where('state_id', $stateId)
            ->where('name', $value)
            ->when($this->route('city'), fn ($q, City $c) => $q->whereKeyNot($c->id))
            ->exists();

        if ($taken) {
            $fail('This state already has a city with this name. Edit that city instead.');
        }
    }
}
