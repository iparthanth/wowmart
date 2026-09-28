<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/** An announcement staff sent to selected users; shown on the storefront. */
#[Fillable(['notification_type_id', 'content', 'link'])]
class CustomNotification extends Model
{
    public function type(): BelongsTo
    {
        return $this->belongsTo(NotificationType::class, 'notification_type_id');
    }

    /** @return BelongsToMany<User, $this> */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'custom_notification_user')
            ->withPivot('read_at')
            ->withTimestamps();
    }
}
