<?php

namespace App\Http\Core\Classes\Catalog;

/**
 * The name of a catalogue row in the reader's own language.
 *
 * Corridors are labelled from three different tables — `sub_services`
 * (`name` / `name_en`), `cities` (`name` / `name_on_google_map`, there is no
 * `en_name` column) and `services` (`title` / `title_en`) — and every caller
 * was reading the native column directly. An English-speaking rider therefore
 * booked "استقبال من المطار · دمشق ← حمص", and an Arabic panel could show a
 * latin city name, purely depending on which column a given screen happened to
 * pick. One resolver keeps a corridor reading the same way everywhere.
 */
class LocalizedName
{
    /** Native-language fields first, then their latin counterparts. */
    private const NATIVE = ['name', 'title'];
    private const LATIN = ['name_en', 'title_en', 'name_on_google_map'];

    public static function of($model, ?bool $arabic = null): ?string
    {
        if ($model === null) {
            return null;
        }

        $arabic = $arabic ?? (app()->getLocale() === 'ar');

        $native = self::first($model, self::NATIVE);
        $latin = self::first($model, self::LATIN);

        // Fall back to the other language rather than to nothing: a row with
        // only one of the two is still better named than by its id.
        return $arabic ? ($native ?? $latin) : ($latin ?? $native);
    }

    /** "Damascus → Homs" in the reader's language, ready to display. */
    public static function corridor($from, $to, ?bool $arabic = null): string
    {
        $arabic = $arabic ?? (app()->getLocale() === 'ar');

        $left = self::of($from, $arabic) ?? '—';
        $right = self::of($to, $arabic) ?? '—';

        return $arabic ? $left . ' ← ' . $right : $left . ' → ' . $right;
    }

    private static function first($model, array $fields): ?string
    {
        foreach ($fields as $field) {
            $value = is_array($model) ? ($model[$field] ?? null) : ($model->{$field} ?? null);

            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return null;
    }
}
