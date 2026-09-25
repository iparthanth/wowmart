<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * The customer form (create and edit) and the API's create/replace body.
 *
 * user_type, banned and email_verified_at have no rule here, so validated()
 * never carries them: a posted user_type=admin is dropped, not obeyed.
 */
class CustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // the route group already requires an admin
    }

    protected function prepareForValidation(): void
    {
        $email = $this->input('email');
        $code = $this->input('country_code');
        $code = is_string($code) && trim($code) !== '' ? trim($code) : ($code ?? '+880');

        $this->merge([
            'email' => is_string($email) ? (trim($email) ?: null) : $email,
            'phone' => $this->normalisePhone($this->input('phone'), is_string($code) ? $code : ''),
            'country_code' => $code,
        ]);
    }

    /**
     * Stored exactly as the storefront stores it (no separators, no country prefix,
     * no trunk 0) so a customer added here can sign in there with the same number.
     */
    private function normalisePhone(mixed $phone, string $code): mixed
    {
        if (! is_string($phone)) {
            return $phone; // null, or junk for the 'string' rule to report
        }

        $phone = preg_replace('/^(\+|00)/', '', preg_replace('/[\s\-().]+/', '', $phone));
        $prefix = ltrim($code, '+');

        if ($prefix !== '' && str_starts_with($phone, $prefix) && strlen($phone) - strlen($prefix) >= 6) {
            $phone = substr($phone, strlen($prefix));
        }

        return ltrim($phone, '0') ?: null;
    }

    public function rules(): array
    {
        $customer = $this->route('customer');

        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['bail', 'nullable', 'required_without:phone', 'string', 'email', 'max:190', $this->uniqueEmail(...)],
            // The storefront signs in by phone alone, and users.phone is unique on its own.
            'phone' => ['bail', 'nullable', 'string', 'regex:/^\d{6,15}$/', Rule::unique('users', 'phone')->ignore($customer?->id)],
            'country_code' => ['required', 'string', 'regex:/^\+\d{1,4}$/'],
            // Edit: blank keeps the current password.
            'password' => [$customer ? 'nullable' : 'required', 'string', 'min:8', 'max:255', 'confirmed'],
        ];
    }

    /** MySQL compares emails case-insensitively but SQLite does not, so compare lowercase on both. */
    private function uniqueEmail(string $attribute, mixed $value, Closure $fail): void
    {
        $taken = User::whereRaw('lower(email) = ?', [mb_strtolower($value)])
            ->when($this->route('customer'), fn ($q, User $customer) => $q->whereKeyNot($customer->id))
            ->exists();

        if ($taken) {
            $fail('Another account already uses this email address.');
        }
    }

    public function messages(): array
    {
        return [
            'email.required_without' => 'Enter an email address or a phone number - the customer signs in with one of them.',
            'phone.regex' => 'Enter the phone number as 6 to 15 digits, like 01711 000000.',
            'phone.unique' => 'Another account already uses this phone number.',
            'country_code.regex' => 'Enter the country code as + and digits, like +880.',
            'password.confirmed' => 'The two passwords do not match. Type the same password in both boxes.',
        ];
    }

    public function attributes(): array
    {
        return ['country_code' => 'country code'];
    }
}
