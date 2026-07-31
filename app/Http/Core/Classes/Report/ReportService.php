<?php

namespace App\Http\Core\Classes\Report;

use App\Http\Core\Classes\Ledger\LedgerService;
use App\Http\Core\Const\Ledger\AccountType;
use App\Http\Core\Const\Ledger\Direction;
use App\Http\Core\Const\Ledger\LedgerKind;
use App\Http\Core\Const\Ledger\OwnerType;
use App\Models\CommissionSnapshot;
use App\Models\LedgerTransaction;
use App\Models\OfficeSubscription;
use Throwable;

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
            'subscription_paid_minor' => $this->subscriptionsMinor($currency, $officeId),
            'subscription_payments' => $this->subscriptionCount($currency, $officeId),
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
            // In a subscription country this — not the ride commission — is
            // most of what the platform earns. Reporting only the snapshots
            // said a country billing every office monthly earned nothing.
            'subscription_revenue_minor' => $this->subscriptionsMinor($currency),
            'subscription_payments' => $this->subscriptionCount($currency),
        ];
    }

    /**
     * Subscription money booked in the ledger, for the whole country or one
     * office. A country whose shard has not taken the subscription tables yet
     * reports zero rather than failing the whole report.
     */
    private function subscriptionsMinor(string $currency, ?int $officeId = null): int
    {
        try {
            return (int) $this->subscriptionEntries($currency, $officeId)->sum('amount_minor');
        } catch (Throwable $e) {
            return 0;
        }
    }

    private function subscriptionCount(string $currency, ?int $officeId = null): int
    {
        try {
            return (int) $this->subscriptionEntries($currency, $officeId)->count();
        } catch (Throwable $e) {
            return 0;
        }
    }

    private function subscriptionEntries(string $currency, ?int $officeId = null)
    {
        $subscriptionIds = $officeId === null
            ? null
            : OfficeSubscription::query()->where('office_id', $officeId)->pluck('id')->all();

        return LedgerTransaction::query()
            ->where('ledger_transactions.kind', LedgerKind::SUBSCRIPTION)
            ->where('ledger_transactions.currency_code', $currency)
            ->when($subscriptionIds !== null, fn ($q) => $q->whereIn('ledger_transactions.reference_id', $subscriptionIds ?: [0]))
            ->join('ledger_entries', 'ledger_entries.transaction_id', '=', 'ledger_transactions.id')
            ->join('ledger_accounts', 'ledger_accounts.id', '=', 'ledger_entries.account_id')
            ->where('ledger_accounts.account_type', AccountType::REVENUE)
            ->where('ledger_entries.direction', Direction::CREDIT)
            ->select('ledger_entries.amount_minor');
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
