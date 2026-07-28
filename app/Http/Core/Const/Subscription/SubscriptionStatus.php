<?php

namespace App\Http\Core\Const\Subscription;

class SubscriptionStatus
{
    const TRIALING = 'trialing';
    const ACTIVE = 'active';
    const PAST_DUE = 'past_due';
    const CANCELED = 'canceled';
    const ENDED = 'ended';

    const ENTITLED = [self::TRIALING, self::ACTIVE, self::PAST_DUE];

    const ALL = [self::TRIALING, self::ACTIVE, self::PAST_DUE, self::CANCELED, self::ENDED];

    public static function isValid(string $status): bool
    {
        return in_array($status, self::ALL, true);
    }

    public static function grantsAccess(string $status): bool
    {
        return in_array($status, self::ENTITLED, true);
    }
}
