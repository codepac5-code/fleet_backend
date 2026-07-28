<?php

namespace App\Http\Core\Classes\Ledger;

use App\Http\Services\User\Support\Presenters\MoneyPresenter;
use App\Models\Country;
use App\Models\Driver;

/**
 * Resolves the currency a driver operates in **from the driver's country**, so
 * the home dock, wallet and earnings dashboards format money in the right
 * currency. The `countries` table maps a country → `currency_code`.
 *
 * Resolution order (most authoritative driver attribute first):
 *   1. Driver dial code            → countries.phone_code
 *   2. Driver country (iso2/name)  → countries.iso2 / name / en_name
 *   3. Request country header       → countries.iso2  (X-Country)
 *   4. Default country's currency, else the platform default.
 */
class DriverCurrency
{
    public static function resolve(Driver $driver, ?string $countryIso = null): string
    {
        // 1) Dial code → phone_code. Stored with or without a leading '+', so
        //    match both forms (e.g. "974" and "+974").
        $dial = ltrim((string) ($driver->dialCode ?? ''), '+');
        if ($dial !== '') {
            $code = Country::query()
                ->whereIn('phone_code', [$dial, '+' . $dial])
                ->value('currency_code');
            if (self::valid($code)) {
                return strtoupper($code);
            }
        }

        // 2) Driver's stored country — an ISO-2 code (any case) or a name.
        $country = trim((string) ($driver->country ?? ''));
        if ($country !== '') {
            $code = self::byCountry($country);
            if (self::valid($code)) {
                return strtoupper($code);
            }
        }

        // 3) Request country header (X-Country), e.g. "QA".
        if ($countryIso !== null && $countryIso !== '') {
            $code = self::byCountry($countryIso);
            if (self::valid($code)) {
                return strtoupper($code);
            }
        }

        // 4) Platform default country, else the default currency.
        $code = Country::query()->where('is_default', true)->value('currency_code');

        return self::valid($code) ? strtoupper($code) : MoneyPresenter::currency(null)['code'];
    }

    /** Currency for an ISO-2 code (any case) or a country name (localized/EN). */
    private static function byCountry(string $value): ?string
    {
        return Country::query()
            ->whereRaw('LOWER(iso2) = ?', [strtolower($value)])
            ->orWhere('name', $value)
            ->orWhere('en_name', $value)
            ->value('currency_code');
    }

    private static function valid(?string $code): bool
    {
        return is_string($code) && $code !== '';
    }
}
