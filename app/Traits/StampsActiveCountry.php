<?php

namespace App\Traits;

use App\Http\Core\GeoServices\ShardContext;
use App\Http\Core\GeoServices\ShardManager;
use Throwable;

/**
 * Stamps `country_code` on a GLOBAL-table row from the active country at creation,
 * so rows whose foreign keys (office/booking ids) repeat across shards stay
 * country-isolatable on read. Best-effort — a row is never blocked if the country
 * cannot be resolved (it simply stays null and is treated as cross-country).
 */
trait StampsActiveCountry
{
    public static function bootStampsActiveCountry(): void
    {
        static::creating(function ($model) {
            if (! empty($model->country_code)) {
                return;
            }

            $code = self::activeCountryCode();

            // Only touch the attribute when a country actually resolves — leaving
            // it unset otherwise keeps the INSERT column-list unchanged for callers
            // whose schema predates the column.
            if ($code !== null) {
                $model->country_code = $code;
            }
        });
    }

    public static function activeCountryCode(): ?string
    {
        try {
            $node = ShardManager::current() ?? ShardContext::current();
            $code = $node->country_code ?? null;

            return $code !== null && $code !== '' ? strtolower((string) $code) : null;
        } catch (Throwable $e) {
            return null;
        }
    }
}
