<?php

namespace App\Http\Core\Classes\Ledger;

use App\Http\Core\Exceptions\DomainException;
use App\Models\Currency;

/**
 * Converts money between currencies using the admin-maintained rates in the
 * `currencies` table.
 *
 * Convention (matches the existing rows): `exchange_rate` is the number of units
 * of that currency equal to 1 unit of the DEFAULT currency. So A→B is
 * `amount * (rate_B / rate_A)`.
 *
 * This exists for one job: a Syrian rider tops up in USD (Stripe has no SYP),
 * and the charge is converted to an SYP wallet credit. It REFUSES to convert
 * when either rate is unset (0) — SYP↔USD has no reliable feed, so a missing
 * admin rate must fail loudly, never silently charge a wrong amount.
 */
class CurrencyConverter
{
    /** Convert a minor-unit amount from one currency to another. */
    public function convertMinor(int $amountMinor, string $from, string $to): int
    {
        $from = strtoupper($from);
        $to = strtoupper($to);

        if ($from === $to) {
            return $amountMinor;
        }

        $fromCur = $this->currency($from);
        $toCur = $this->currency($to);

        $rateFrom = (float) $fromCur->exchange_rate;
        $rateTo = (float) $toCur->exchange_rate;

        if ($rateFrom <= 0 || $rateTo <= 0) {
            // e.g. SYP seeded with rate 0 until an admin sets it.
            throw DomainException::make('fx_rate_unset', 422);
        }

        // Move to major units, apply the cross rate, then back to the target's
        // minor units — so currencies with different decimals still convert
        // correctly.
        $fromMajor = $amountMinor / (10 ** (int) $fromCur->decimals);
        $toMajor = $fromMajor * ($rateTo / $rateFrom);

        return (int) round($toMajor * (10 ** (int) $toCur->decimals));
    }

    /** The rate is usable (both currencies exist and carry a positive rate). */
    public function canConvert(string $from, string $to): bool
    {
        try {
            $this->convertMinor(0 === 0 ? 100 : 100, $from, $to);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    private function currency(string $code): Currency
    {
        $currency = Currency::query()->where('code', $code)->where('is_active', true)->first();

        if ($currency === null) {
            throw DomainException::make('currency_not_found', 422);
        }

        return $currency;
    }
}
