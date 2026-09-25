<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A named bundle of permissions that staff members sign in with.
 *
 * The permission strings are keys from config/permissions.php; what each key
 * opens lives there, not here. An admin has no role at all - roles only ever
 * narrow a staff member's panel.
 */
class Role extends Model
{
    protected $fillable = ['name'];

    /** @return HasMany<User> */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /** @return HasMany<RolePermission> */
    public function permissions(): HasMany
    {
        return $this->hasMany(RolePermission::class);
    }

    /** @return list<string> */
    public function permissionKeys(): array
    {
        return $this->permissions->pluck('permission')->all();
    }
}
