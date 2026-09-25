<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * Key/value site configuration: many settings screens, one table.
 * 
 * An empty `lang` means the value applies to every language.
 */
#[Fillable(['type', 'value', 'lang'])]
class BusinessSetting extends Model
{
    public static function get(string $key, mixed $default = null, string $lang = ''): mixed
    {
        return static::where('type', $key)->where('lang', $lang)->value('value') ?? $default;
    }

    public static function put(string $key, mixed $value, string $lang = ''): void
    {
        static::updateOrCreate(['type' => $key, 'lang' => $lang], ['value' => $value]);
    }
}
