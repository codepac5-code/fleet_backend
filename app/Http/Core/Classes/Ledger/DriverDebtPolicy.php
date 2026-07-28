<?php

namespace App\Http\Core\Classes\Ledger;

use App\Http\Core\Classes\Settings\AppSettings;

/**
 * The configurable driver debt ceiling. A driver accumulates dues when a cash
 * commission can't be fully covered by their prepaid wallet (see
 * {@see FleetWalletService::chargeCommission}). Past a ceiling the driver is
 * blocked from taking new rides until they top up and settle — this replaces the
 * old hardcoded 5000/1000/500 thresholds with a per-currency setting.
 *
 * Settings (via {@see AppSettings} / site settings):
 *   driver_debt_ceiling_minor_<CUR>  per-currency override (minor units)
 *   driver_debt_ceiling_minor        global fallback
 * A ceiling of 0 (the default) disables the cap entirely.
 */
class DriverDebtPolicy
{
    public function __construct(private FleetWalletService $wallet)
    {
    }

    public function ceilingMinor(string $currency): int
    {
        $specific = AppSettings::int('driver_debt_ceiling_minor_' . strtoupper($currency), -1);

        if ($specific >= 0) {
            return $specific;
        }

        return max(0, AppSettings::int('driver_debt_ceiling_minor', 0));
    }

    public function outstandingMinor(int $driverId, string $currency): int
    {
        return max(0, $this->wallet->duesBalanceMinor($driverId, $currency));
    }

    /** Blocked when a ceiling is configured (> 0) and dues meet or exceed it. */
    public function isBlocked(int $driverId, string $currency): bool
    {
        $ceiling = $this->ceilingMinor($currency);

        return $ceiling > 0 && $this->outstandingMinor($driverId, $currency) >= $ceiling;
    }
}
