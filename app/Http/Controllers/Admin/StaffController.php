<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Services\StaffService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StaffController extends Controller
{
    public function __construct(private readonly StaffService $staff) {}

    public function index(Request $request)
    {
        return view('admin.staff.index', [
            'members' => User::query()
                ->whereIn('user_type', [UserType::Admin, UserType::Staff])
                ->with('role')
                ->orderByRaw('case when user_type = ? then 0 else 1 end', [UserType::Admin->value])
                ->orderBy('name')
                ->paginate(20)
                ->withQueryString(),
            'roles' => Role::query()->orderBy('name')->get(),
            'newMember' => new User,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $this->staff->create($data);

        return redirect()
            ->route('admin.staff.index')
            ->with('status', 'Staff member added.');
    }

    public function edit(User $member)
    {
        return view('admin.staff.edit', [
            'member' => $member->load('role'),
            'roles' => Role::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, User $member)
    {
        $data = $this->validated($request, $member);

        try {
            $this->staff->update($member, $data);
        } catch (\DomainException $e) {
            return redirect()->route('admin.staff.index')->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.staff.index')
            ->with('status', 'Staff member updated.');
    }

    public function destroy(Request $request, User $member)
    {
        $actor = $request->user();

        if (! $actor instanceof Authenticatable) {
            abort(403);
        }

        try {
            $this->staff->delete($actor, $member);
        } catch (\DomainException $e) {
            return redirect()->route('admin.staff.index')->with('error', $e->getMessage());
        }

        return redirect()->route('admin.staff.index')->with('status', 'Staff member removed.');
    }

    /**
     * @return array{name: string, email: string, password: string|null, is_admin: bool, role_id: int|null}
     */
    private function validated(Request $request, ?User $member = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($member?->id)],
            'password' => [$member === null ? 'required' : 'nullable', 'string', 'min:8'],
            'is_admin' => ['sometimes', 'boolean'],
            'role_id' => ['nullable', Rule::exists('roles', 'id')],
        ]);

        $isAdmin = (bool) ($validated['is_admin'] ?? false);

        if (! $isAdmin && ($validated['role_id'] ?? null) === null) {
            throw ValidationException::withMessages([
                'role_id' => 'Choose a role, or tick administrator.',
            ]);
        }

        if ($isAdmin && ($validated['role_id'] ?? null) !== null) {
            throw ValidationException::withMessages([
                'role_id' => 'An administrator holds no role - clear the role or untick administrator.',
            ]);
        }

        return [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'] ?? null,
            'is_admin' => $isAdmin,
            'role_id' => $isAdmin ? null : (int) $validated['role_id'],
        ];
    }
}
