<?php

namespace App\Services;

use App\Models\Role;
use DomainException;
use Illuminate\Support\Facades\DB;

/** Every write to a role. Input is validated already (RoleRequest). */
class RoleService
{
    /**
     * @param array{name: string, permissions: list<string>} $data
     */
    public function create(array $data): Role
    {
        return DB::transaction(function () use ($data) {
            $role = Role::create(['name' => $data['name']]);
            $this->sync($role, $data['permissions'] ?? []);

            return $role;
        });
    }

    /**
     * A full replacement: the permission list sent is the list stored, so an
     * emptied form empties the role.
     *
     * @param array{name?: string, permissions?: list<string>} $data
     */
    public function update(Role $role, array $data): Role
    {
        return DB::transaction(function () use ($role, $data) {
            if (array_key_exists('name', $data)) {
                $role->name = $data['name'];
            }

            if (array_key_exists('permissions', $data)) {
                $this->sync($role, $data['permissions']);
            }

            $role->save();

            return $role;
        });
    }

    /**
     * @throws DomainException while any staff member signs in with the role
     */
    public function delete(Role $role): void
    {
        DB::transaction(function () use ($role) {
            $count = $role->users()->count();

            if ($count > 0) {
                throw new DomainException(sprintf(
                    '%s this role; assign them a different role first.',
                    $count === 1 ? '1 staff member uses' : $count.' staff members use',
                ));
            }

            $role->delete(); // role_permissions cascade
        });
    }

    /** @param list<string> $keys */
    private function sync(Role $role, array $keys): void
    {
        $role->permissions()->delete();
        $role->permissions()->createMany(array_map(fn (string $key) => ['permission' => $key], array_unique($keys)));
        $role->unsetRelation('permissions');
    }
}
