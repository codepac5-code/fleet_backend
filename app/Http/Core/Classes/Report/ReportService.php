<?php

namespace App\Http\Core\Classes\Report;

use App\Http\Core\Classes\Ledger\LedgerService;
use App\Http\Core\Const\Ledger\AccountType;
use App\Http\Core\Const\Ledger\OwnerType;
use App\Models\CommissionSnapshot;

class ReportService
{
    public function __construct(private LedgerService $ledger)
    {
    }

    public function officeSummary(int $officeId, string $currency): array
    {
        $base = fn () => CommissionSnapshot::query()
            ->where('office_id', $officeId)
            ->where('currency_code', $currency);

        return [
            'office_id' => $officeId,
            'currency_code' => $currency,
            'rides' => (int) $base()->count(),
            'gross_minor' => (int) $base()->sum('total_minor'),
            'office_earned_minor' => (int) $base()->sum('office_minor'),
            'fleet_commission_minor' => (int) $base()->sum('fleet_minor'),
            'driver_paid_minor' => (int) $base()->sum('driver_minor'),
            'revenue_balance_minor' => $this->ledger->ownerBalanceMinor(OwnerType::OFFICE, $officeId, AccountType::REVENUE, $currency),
        ];
    }

    public function fleetSummary(string $currency): array
    {
        $base = fn () => CommissionSnapshot::query()->where('currency_code', $currency);

        return [
            'currency_code' => $currency,
            'rides' => (int) $base()->count(),
            'gross_minor' => (int) $base()->sum('total_minor'),
            'fleet_revenue_minor' => (int) $base()->sum('fleet_minor'),
            'office_payouts_minor' => (int) $base()->sum('office_minor'),
            'driver_payouts_minor' => (int) $base()->sum('driver_minor'),
            'revenue_balance_minor' => $this->ledger->ownerBalanceMinor(OwnerType::FLEET, OwnerType::FLEET_OWNER_ID, AccountType::REVENUE, $currency),
        ];
    }

    public function driverEarnings(int $driverId, string $currency): array
    {
        $base = fn () => CommissionSnapshot::query()
            ->where('driver_id', $driverId)
            ->where('currency_code', $currency);

        return [
            'driver_id' => $driverId,
            'currency_code' => $currency,
            'rides' => (int) $base()->count(),
            'earned_minor' => (int) $base()->sum('driver_minor'),
        ];
    }
}
