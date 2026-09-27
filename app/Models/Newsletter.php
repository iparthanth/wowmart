<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/** A newsletter the admin composed; sending is queued and stamped on `sent_at`. */
#[Fillable(['subject', 'body', 'segment', 'sent_at'])]
class Newsletter extends Model
{
    public const SEGMENTS = ['all', 'subscribers', 'customers'];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }
}
