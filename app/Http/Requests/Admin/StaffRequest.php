<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * The staff form (inline on the list, and the edit page) and the API's
 * create/replace body. `role_id` is required for a staff member and must be
 * absent for an administrator - admins hold no role.
 */
class StaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // the route group already gates the panel
    }

    public function rules(): array
    {
        $editing = $this->route('staff') instanceof User;

        return [
            'name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'string', 'email:rfc', 'max:255', Rule::unique('users', 'email')->ignore($this->route('staff'))],
            'password' => [$editing ? 'nullable' : 'required', 'string', Password::min(8)],
            'is_admin' => ['sometimes', 'boolean'],
            'role_id' => [
                'bail',
                Rule::excludeIf((bool) $this->boolean('is_admin')),
                $editing ? 'nullable' : 'required',
                'integer',
                Rule::exists('roles', 'id'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'role_id.required' => 'Choose the role this staff member signs in with.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // An unticked checkbox sends nothing; normalise so boolean() is exact.
        $this->merge(['is_admin' => $this->boolean('is_admin')]);
    }

    /** After validation this is a clean create/replace payload for StaffService. */
    public function payload(): array
    {
        $data = [
            'name' => (string) $this->validated('name'),
            'email' => (string) $this->validated('email'),
            'is_admin' => $this->boolean('is_admin'),
        ];

        if (is_string($password = $this->validated('password')) && $password !== '') {
            $data['password'] = $password;
        }

        return array_filter($data + ['role_id' => $this->validated('role_id')], fn ($v) => $v !== null);
    }

    public function withValidator(Closure $validator): void
    {
        // Only meaningful on the web form; harmless for API clients.
        $validator->after(function ($validator): void {
            if ($this->boolean('is_admin') && $this->filled('role_id')) {
                $validator->errors()->add('role_id', 'An administrator holds no role - leave the role empty.');
            }
        });
    }
}
