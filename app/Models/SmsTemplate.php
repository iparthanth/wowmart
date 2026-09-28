<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/** An editable SMS text with its provider template reference and activation flag. */
#[Fillable(['key', 'name', 'body', 'template_id', 'status'])]
class SmsTemplate extends Model
{
    protected function casts(): array
    {
        return ['status' => 'boolean'];
    }
}
