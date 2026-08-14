<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    private static ?Collection $cache = null;

    public static function get(string $key, mixed $default = null): mixed
    {
        if (static::$cache === null) {
            static::$cache = static::all()->pluck('value', 'key');
        }

        return static::$cache->get($key, $default);
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        static::$cache = null;
    }
}
