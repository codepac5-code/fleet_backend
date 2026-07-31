<?php

namespace App\Http\Services\Panel\Drivers\Logic;

use App\Http\Core\Classes\Ledger\FleetWalletService;
use App\Http\Core\Classes\Subscription\CommissionResolver;
use App\Http\Core\GeoServices\ShardManager;
use App\Models\Driver;
use Throwable;

/**
 * A driver's money in one place: wallet balance, what they owe the fleet, and
 * the commission rate actually applied to their rides — the office's, or their
 * own negotiated override. Read-only and fail-safe; the actions live in their
 * own controllers so each is auditable on its own.
 */
class DriverFinance
{
    public function __construct(
        private FleetWalletService $wallet,
        private CommissionResolver $commission
    ) {
    }

    public function summary(Driver $driver): array
    {
        $currency = ShardManager::currency();
        $override = $driver->commission_rate_override !== null ? (float) $driver->commission_rate_override : null;
        $officeRates = $this->officeRates((int) $driver->officeId);

        return [
            'currency' => $currency,
            'walletMinor' => $this->safe(fn () => $this->wallet->walletBalanceMinor('driver', (int) $driver->id, $currency)),
            'duesMinor' => $this->safe(fn () => $this->wallet->duesBalanceMinor((int) $driver->id, $currency)),
            'officeRate' => $officeRates['office_rate'],
            'fleetRate' => $officeRates['fleet_rate'],
            'plan' => $officeRates['subscription_plan'] ?? null,
            'override' => $override,
            // What settlement will actually use for this driver's next ride.
            'effectiveRate' => $override ?? $officeRates['office_rate'],
        ];
    }

    private function officeRates(int $officeId): array
    {
        try {
            return $this->commission->forOffice($officeId);
        } catch (Throwable $e) {
            return ['fleet_rate' => 0.0, 'office_rate' => 0.0, 'subscription_plan' => null];
        }
    }

    private function safe(callable $read): int
    {
        try {
            return (int) $read();
        } catch (Throwable $e) {
            return 0;
        }
    }
}
