<?php

namespace App\Http\Core\Const\Ledger;

class AccountType
{
    const WALLET = 'wallet';
    const ESCROW = 'escrow';
    const DUES = 'dues';
    const REVENUE = 'revenue';
    const PSP_CLEARING = 'psp_clearing';
    const PAYOUT_CLEARING = 'payout_clearing';

    const CREDIT_NORMAL = [
        self::WALLET,
        self::ESCROW,
        self::REVENUE,
    ];

    public static function all(): array
    {
        return [
            self::WALLET,
            self::ESCROW,
            self::DUES,
            self::REVENUE,
            self::PSP_CLEARING,
            self::PAYOUT_CLEARING,
        ];
    }

    public static function isCreditNormal(string $type): bool
    {
        return in_array($type, self::CREDIT_NORMAL, true);
    }
}
