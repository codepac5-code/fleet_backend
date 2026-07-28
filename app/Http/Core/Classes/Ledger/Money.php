<?php

namespace App\Http\Core\Classes\Ledger;

class Money
{
    public static function toMinor($amount, int $decimals = 2): int
    {
        $factor = (int) pow(10, $decimals);
        return (int) round(((float) $amount) * $factor);
    }

    public static function toDecimal(int $minor, int $decimals = 2): string
    {
        $factor = (int) pow(10, $decimals);
        return number_format($minor / $factor, $decimals, '.', '');
    }

    public static function splitByRates(int $totalMinor, array $rates): array
    {
        $result = [];
        $allocated = 0;
        $keys = array_keys($rates);
        $lastKey = end($keys);

        foreach ($rates as $key => $rate) {
            if ($key === $lastKey) {
                $result[$key] = $totalMinor - $allocated;
            } else {
                $part = (int) round($totalMinor * ($rate / 100));
                $result[$key] = $part;
                $allocated += $part;
            }
        }

        return $result;
    }
}
