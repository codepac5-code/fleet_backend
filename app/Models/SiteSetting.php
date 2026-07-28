<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $connection = 'global';

    protected $table = 'site_settings';

    protected $fillable = ['key', 'value'];

    private static ?array $cache = null;

    public static function val(string $key, $default = null)
    {
        if (self::$cache === null) {
            try {
                self::$cache = self::query()->pluck('value', 'key')->all();
            } catch (\Throwable $e) {
                self::$cache = [];
            }
        }

        $v = self::$cache[$key] ?? null;

        return ($v === null || $v === '') ? $default : $v;
    }

    public static function put(string $key, $value): void
    {
        self::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        self::$cache = null;
    }

    public static function flush(): void
    {
        self::$cache = null;
    }
}
