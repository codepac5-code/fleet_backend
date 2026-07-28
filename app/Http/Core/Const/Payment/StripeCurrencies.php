<?php

namespace App\Http\Core\Const\Payment;

/**
 * Currencies Stripe can settle a PaymentIntent in. Used to decide whether a
 * wallet's local currency can be charged directly, or whether the rider must
 * top up in a supported currency that we then convert to a local credit
 * (e.g. SYP is NOT here — a Syrian rider pays in USD, the wallet is credited in
 * SYP via CurrencyConverter). Sourced from Stripe's own "Invalid currency"
 * error listing.
 */
class StripeCurrencies
{
    public const LIST = [
        'usd', 'aed', 'afn', 'all', 'amd', 'ang', 'aoa', 'ars', 'aud', 'awg', 'azn',
        'bam', 'bbd', 'bdt', 'bgn', 'bhd', 'bif', 'bmd', 'bnd', 'bob', 'brl', 'bsd',
        'bwp', 'byn', 'bzd', 'cad', 'cdf', 'chf', 'clp', 'cny', 'cop', 'crc', 'cve',
        'czk', 'djf', 'dkk', 'dop', 'dzd', 'egp', 'etb', 'eur', 'fjd', 'fkp', 'gbp',
        'gel', 'gip', 'gmd', 'gnf', 'gtq', 'gyd', 'hkd', 'hnl', 'hrk', 'htg', 'huf',
        'idr', 'ils', 'inr', 'isk', 'jmd', 'jod', 'jpy', 'kes', 'kgs', 'khr', 'kmf',
        'krw', 'kwd', 'kyd', 'kzt', 'lak', 'lbp', 'lkr', 'lrd', 'lsl', 'mad', 'mdl',
        'mga', 'mkd', 'mmk', 'mnt', 'mop', 'mur', 'mvr', 'mwk', 'mxn', 'myr', 'mzn',
        'nad', 'ngn', 'nio', 'nok', 'npr', 'nzd', 'omr', 'pab', 'pen', 'pgk', 'php',
        'pkr', 'pln', 'pyg', 'qar', 'ron', 'rsd', 'rub', 'rwf', 'sar', 'sbd', 'scr',
        'sek', 'sgd', 'shp', 'sle', 'sos', 'srd', 'std', 'szl', 'thb', 'tjs', 'tnd',
        'top', 'try', 'ttd', 'twd', 'tzs', 'uah', 'ugx', 'uyu', 'uzs', 'vnd', 'vuv',
        'wst', 'xaf', 'xcd', 'xcg', 'xof', 'xpf', 'yer', 'zar', 'zmw', 'usdc', 'btn',
        'ghs', 'eek', 'lvl', 'svc', 'vef', 'ltl', 'sll', 'mro',
    ];

    public static function isSupported(string $code): bool
    {
        return in_array(strtolower($code), self::LIST, true);
    }
}
