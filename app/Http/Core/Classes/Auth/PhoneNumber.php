<?php

namespace App\Http\Core\Classes\Auth;

class PhoneNumber
{
    private const DIAL_CODES = [
        '974', '971', '966', '973', '968', '965', '963', '962', '961', '967', '964', '970',
        '20', '90', '212', '213', '216', '218', '249', '98',
        '1', '44', '33', '49', '39', '34', '31', '46', '7', '86', '91', '92', '880',
    ];

    public static function normalize(string $raw): ?string
    {
        $digits = preg_replace('/[^0-9]/', '', $raw);

        if ($digits === '' || strlen($digits) < 8 || strlen($digits) > 15) {
            return null;
        }

        return '+' . $digits;
    }

    public static function split(string $e164): array
    {
        $digits = ltrim($e164, '+');

        $codes = self::DIAL_CODES;
        usort($codes, static fn ($a, $b) => strlen($b) <=> strlen($a));

        foreach ($codes as $code) {
            if (str_starts_with($digits, $code) && strlen($digits) > strlen($code)) {
                return ['+' . $code, substr($digits, strlen($code))];
            }
        }

        return ['', $digits];
    }

    public static function mask(string $e164): string
    {
        if (strlen($e164) <= 4) {
            return $e164;
        }

        return substr($e164, 0, -2) . '••';
    }
}
