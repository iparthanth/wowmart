<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One granted permission key on a role. Pure pivot - no timestamps. */
class RolePermission extends Model
{
    public $timestamps = false;

    protected $fillable = ['role_id', 'permission'];

    /** @return BelongsTo<Role, $this> */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
