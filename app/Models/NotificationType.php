<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** A storefront notification icon: name, picture and default message text. */
#[Fillable(['name', 'image', 'default_text', 'status'])]
class NotificationType extends Model
{
    protected function casts(): array
    {
        return ['status' => 'boolean'];
    }

    /** The `image` column holds the upload id; this loads the file itself. */
    public function imageUpload(): BelongsTo
    {
        return $this->belongsTo(Upload::class, 'image');
    }

    /** @return HasMany<CustomNotification, $this> */
    public function customNotifications(): HasMany
    {
        return $this->hasMany(CustomNotification::class, 'notification_type_id');
    }
}
