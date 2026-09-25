<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RoleRequest;
use App\Models\Role;
use App\Services\PermissionService;
use App\Services\RoleService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/** Roles and the permission keys each one holds. */
class RoleController extends Controller
{
    public function __construct(private readonly RoleService $roles, private readonly PermissionService $permissions) {}

    /** The list, with the "Add a role" form beside it. */
    public function index(): View
    {
        return view('admin.roles.index', [
            'roles' => Role::orderBy('name')->withCount('users')->with('permissions')->paginate(20),
            'registry' => $this->permissions->all(),
            'newRole' => new Role,
        ]);
    }

    public function store(RoleRequest $request): RedirectResponse
    {
        $role = $this->roles->create($request->validated());

        return redirect()->route('admin.roles.edit', $role)->with('status', sprintf('Role "%s" created.', $role->name));
    }

    public function edit(Role $role): View
    {
        return view('admin.roles.edit', [
            'role' => $role->load('permissions'),
            'registry' => $this->permissions->all(),
        ]);
    }

    public function update(RoleRequest $request, Role $role): RedirectResponse
    {
        $this->roles->update($role, $request->validated());

        return redirect()->route('admin.roles.edit', $role)->with('status', 'Changes saved.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        try {
            $this->roles->delete($role);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.roles.index')->with('status', sprintf('Role "%s" deleted.', $role->name));
    }
}
