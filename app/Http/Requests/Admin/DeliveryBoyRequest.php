<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/** The delivery boy form (inline on the list, and the edit page) and the API's create/replace body. */
class DeliveryBoyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // the route group already requires an admin
    }

    public function rules(): array
    {
        $boy = $this->route('delivery_boy');

        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['bail', 'required', 'string', 'email', 'max:255', $this->uniqueEmail(...)],
            'phone' => ['bail', 'required', 'string', 'max:32', $this->uniquePhone(...)],
            'password' => [
                $boy ? 'nullable' : 'required',
                Password::min(8),
            ],
        ];
    }

    private function uniqueEmail(string $attribute, mixed $value, Closure $fail): void
    {
        $taken = User::where('email', $value)
            ->when($this->route('delivery_boy'), fn ($q, User $b) => $q->whereKeyNot($b->id))
            ->exists();

        if ($taken) {
            $fail('That email is already on an account.');
        }
    }

    private function uniquePhone(string $attribute, mixed $value, Closure $fail): void
    {
        $taken = User::where('phone', $value)
            ->when($this->route('delivery_boy'), fn ($q, User $b) => $q->whereKeyNot($b->id))
            ->exists();

        if ($taken) {
            $fail('That phone number is already on an account.');
        }
    }

    /** @return array{name: string, email: string, phone: string, password: string|null} */
    public function payload(): array
    {
        return [
            'name' => $this->validated('name'),
            'email' => $this->validated('email'),
            'phone' => $this->validated('phone'),
            'password' => $this->validated('password', '') !== '' ? $this->validated('password') : null,
        ];
    }
}
