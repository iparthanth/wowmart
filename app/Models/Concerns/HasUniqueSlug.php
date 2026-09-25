<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * For models with a unique `slug` column that is set once, from the name, and
 * then kept: renaming must not break links already shared or indexed.
 */
trait HasUniqueSlug
{
    // ponytail: check-then-insert can race; two simultaneous saves of the same
    // name hit the unique index and one fails. Retry on that error if it ever matters.
    public static function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: Str::snake(class_basename(static::class)); // Bangla-script names slug to ''
        $slug = $base;

        for ($n = 2; static::where('slug', $slug)->exists(); $n++) {
            $slug = "{$base}-{$n}";
        }

        return $slug;
    }
}
