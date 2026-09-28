<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/** A message sent through the storefront contact form. */
#[Fillable(['name', 'email', 'phone', 'query', 'reply', 'status'])]
class ContactMessage extends Model
{
    public const STATUSES = ['open', 'answered'];

    /** Mirrors the migration default so fresh instances carry it before insert. */
    protected $attributes = ['status' => 'open'];
}
