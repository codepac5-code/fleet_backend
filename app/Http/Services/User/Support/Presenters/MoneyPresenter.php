<?php

namespace App\Http\Services\User\Support\Presenters;

use App\Http\Core\Classes\Ledger\Money;
use App\Models\Currency;

class MoneyPresenter
{
    public static function currency(?string $code): array
    {
        // Bind the currency to the caller's COUNTRY (X-Country) when no explicit
        // code is given: countries.iso2 → currency_code (e.g. QA → QAR, SA → SAR).
        if ($code === null || $code === '') {
            $country = strtolower((string) request()->header('X-Country', ''));
            if ($country !== '') {
                $byCountry = \Illuminate\Support\Facades\DB::table('countries')
                    ->whereRaw('LOWER(iso2) = ?', [$country])
                    ->value('currency_code');
                if (! empty($byCountry)) {
                    $code = $byCountry;
                }
            }
        }

        $resolved = $code !== null && $code !== ''
            ? strtoupper($code)
            : (Currency::query()->where('is_default', true)->value('code') ?: 'USD');

        $row = Currency::query()->where('code', $resolved)->first();

        return [
            'code' => $resolved,
            'symbol' => $row->symbol ?? $resolved,
            'decimals' => $row !== null ? (int) $row->decimals : 2,
        ];
    }

    public static function decimal(int $minor, int $decimals): float
    {
        return (float) Money::toDecimal($minor, $decimals);
    }
}
