<?php

namespace App\Http\Core\Classes\Settings;

use App\Models\SiteSetting;
use Throwable;

class AppSettings
{
    private static bool $available = true;
    private static array $cache = [];

    public static function int(string $key, int $default): int
    {
        $v = self::raw($key);

        return $v === null || $v === '' ? $default : (int) $v;
    }

    public static function string(string $key, string $default = ''): string
    {
        $v = self::raw($key);

        return $v === null || $v === '' ? $default : (string) $v;
    }

    public static function float(string $key, float $default): float
    {
        $v = self::raw($key);

        return $v === null || $v === '' ? $default : (float) $v;
    }

    private static function raw(string $key)
    {
        if (! self::$available) {
            return null;
        }

        if (array_key_exists($key, self::$cache)) {
            return self::$cache[$key];
        }

        try {
            return self::$cache[$key] = SiteSetting::val($key);
        } catch (Throwable $e) {
            self::$available = false;

            return null;
        }
    }
}
