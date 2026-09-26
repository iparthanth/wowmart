<?php

namespace App\Services;

use App\Enums\UserType;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Staff membership lives on the shared users table, typed with user_type.
 * Administrators hold no role; every other staff member holds exactly one.
 *
 * user_type and role_id are deliberately guarded on the model, so they are set
 * as explicit properties here (mass assignment would silently discard them).
 */
class StaffService
{
    /**
     * @param  array{name: string, email: string, password: string|null, is_admin: bool, role_id: int|null}  $data
     */
    public function create(array $data): User
    {
        $member = new User([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'] ?? null,
        ]);

        $member->user_type = $data['is_admin'] ? UserType::Admin : UserType::Staff;
        $member->role_id = $data['role_id'] ?? null;
        $member->save();

        return $member;
    }

    /**
     * @param  array{name: string, email: string, password: string|null, is_admin: bool, role_id: int|null}  $data
     *
     * @throws \DomainException when demoting the last administrator
     */
    public function update(User $member, array $data): void
    {
        if ($member->user_type === UserType::Admin
            && ! $data['is_admin']
            && self::adminCount() === 1) {
            throw new \DomainException('The last administrator cannot be demoted.');
        }

        $member->fill([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        $member->user_type = $data['is_admin'] ? UserType::Admin : UserType::Staff;
        $member->role_id = $data['role_id'] ?? null;

        if (($data['password'] ?? null) !== null && $data['password'] !== '') {
            $member->password = $data['password'];
        }

        $member->save();
    }

    /**
     * @throws \DomainException when removing yourself or the last administrator
     */
    public function delete(Authenticatable $actor, User $member): void
    {
        if ($member->is($actor)) {
            throw new \DomainException('You cannot remove your own account.');
        }

        if ($member->user_type === UserType::Admin && self::adminCount() === 1) {
            throw new \DomainException('The last administrator cannot be removed.');
        }

        $member->delete();
    }

    private static function adminCount(): int
    {
        return User::where('user_type', UserType::Admin)->count();
    }
}
