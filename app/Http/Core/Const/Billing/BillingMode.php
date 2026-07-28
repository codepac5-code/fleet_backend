<?php

namespace App\Http\Core\Const\Billing;

class BillingMode
{
    const COMMISSION = 'commission';
    const SUBSCRIPTION = 'subscription';

    const ALL = [self::COMMISSION, self::SUBSCRIPTION];

    public static function isValid(string $mode): bool
    {
        return in_array($mode, self::ALL, true);
    }
}
