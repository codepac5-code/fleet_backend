<?php

namespace App\Http\Core\Const\Subscription;

use App\Http\Core\Classes\Settings\AppSettings;

/**
 * What the platform takes when nobody has negotiated anything else.
 *
 * The fare splits three ways: the fleet's cut, the office's cut, and whatever
 * is left, which is the driver's. The operator's stated model is 5 / 95 — five
 * to the platform, ninety-five to the office and its driver together — and the
 * office decides how that 95 is divided with the driver.
 */
class CommissionDefaults
{
    public const FLEET_RATE = 5.0;

    public const SETTING_KEY = 'fleet_commission_rate';

    /** The platform-wide fleet cut, editable in Settings. */
    public static function fleetRate(): float
    {
        return self::clamp(AppSettings::float(self::SETTING_KEY, self::FLEET_RATE));
    }

    /** A percentage that cannot leave the split impossible to compute. */
    public static function clamp(float $rate): float
    {
        return max(0.0, min(100.0, $rate));
    }
}
