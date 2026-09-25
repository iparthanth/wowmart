<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * Reusable text blocks (refund terms, warranty terms) shared across products.
 */
#[Fillable(['type', 'description'])]
class Note extends Model
{}
