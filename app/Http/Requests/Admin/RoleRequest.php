<?php

namespace App\Http\Requests\Admin;

use App\Services\PermissionService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * The role form (inline on the list, and the edit page) and the API's
 * create/replace body. `permissions` is `present`, not `required`: submitting
 * zero checkboxes is how a role is emptied, so an absent key must not fall
 * back to "keep the old ones".
 */
class RoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // the route group already gates the panel
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:60', Rule::unique('roles', 'name')->ignore($this->route('role'))],
            'permissions' => ['present', 'array'],
            'permissions.*' => ['string', Rule::in(app(PermissionService::class)->keys())],
        ];
    }

    public function messages(): array
    {
        return [
            'permissions.*.in' => 'One of the chosen permissions is not in the registry.',
        ];
    }
}
