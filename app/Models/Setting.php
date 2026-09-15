<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['group', 'key', 'value'];

    /** Get a setting value with dot notation, e.g. Setting::get('company.currency'). */
    public static function get(string $groupDotKey, mixed $default = null): mixed
    {
        [$group, $key] = explode('.', $groupDotKey, 2);
        return self::where('group', $group)->where('key', $key)->value('value') ?? $default;
    }

    public static function set(string $group, string $key, mixed $value): void
    {
        self::updateOrCreate(['group' => $group, 'key' => $key], ['value' => $value]);
    }
}
