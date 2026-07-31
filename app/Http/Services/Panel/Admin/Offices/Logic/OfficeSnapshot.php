<?php

namespace App\Http\Services\Panel\Admin\Offices\Logic;

use App\Http\Core\Classes\Catalog\LocalizedName;

use App\Http\Core\Classes\Billing\RegionBilling;
use App\Http\Core\Classes\Ledger\FleetWalletService;
use App\Http\Core\GeoServices\ShardManager;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\CommissionSnapshot;
use App\Models\Office;
use App\Models\OfficeSubscription;
use App\Models\TravelRoutes;
use Carbon\Carbon;
use Throwable;

/**
 * The commercial picture of one office in a single read: what it charges for
 * travel, what it earns, what it owes, and whether it is paying to be here.
 *
 * Every section is best-effort — a pricing screen must still render on a shard
 * where a table has not been provisioned yet.
 */
class OfficeSnapshot
{
    public function __construct(private FleetWalletService $wallet)
    {
    }

    public function for(Office $office): array
    {
        $officeId = (int) $office->id;
        $currency = ShardManager::currency();

        return [
            'currency' => $currency,
            'corridors' => $this->corridors($officeId),
            'earnings' => $this->earnings($officeId),
            'finance' => $this->finance($office, $currency),
            'subscription' => $this->subscription($officeId),
        ];
    }

    /** Fixed travel lines this office publishes — its Travel price list. */
    private function corridors(int $officeId): array
    {
        try {
            $rows = TravelRoutes::on(TenantConnection::current())
                ->where('officeId', $officeId)
                ->with(['subService', 'departureCity', 'arrivalCity'])
                ->orderBy('sub_service_id')
                ->get();
        } catch (Throwable $e) {
            return ['count' => 0, 'min' => null, 'max' => null, 'rows' => []];
        }

        return [
            'count' => $rows->count(),
            'min' => $rows->min('trip_price'),
            'max' => $rows->max('trip_price'),
            'rows' => $rows->map(fn ($r) => [
                'sub_service' => LocalizedName::of($r->subService) ?? '#' . $r->sub_service_id,
                'from' => LocalizedName::of($r->departureCity) ?? '#' . $r->departure_city_id,
                'to' => LocalizedName::of($r->arrivalCity) ?? '#' . $r->arrival_city_id,
                'price' => (float) $r->trip_price,
            ])->all(),
        ];
    }

    /** The office's cut of settled rides, from the frozen commission snapshots. */
    private function earnings(int $officeId): array
    {
        try {
            $base = CommissionSnapshot::on(TenantConnection::current())->where('office_id', $officeId);

            $lifetime = (int) (clone $base)->sum('office_minor');
            $month = (int) (clone $base)->where('created_at', '>=', Carbon::now()->startOfMonth())->sum('office_minor');
            $rides = (int) (clone $base)->count();
        } catch (Throwable $e) {
            return ['lifetimeMinor' => 0, 'monthMinor' => 0, 'rides' => 0];
        }

        return ['lifetimeMinor' => $lifetime, 'monthMinor' => $month, 'rides' => $rides];
    }

    /** Wallet held for the office, and what it still owes the fleet. */
    private function finance(Office $office, string $currency): array
    {
        $walletMinor = 0;

        try {
            $walletMinor = $this->wallet->walletBalanceMinor('office', (int) $office->id, $currency);
        } catch (Throwable $e) {
        }

        return [
            'walletMinor' => $walletMinor,
            // Legacy major-unit columns the office screens have always shown.
            'fleetDues' => (float) ($office->fleetDues ?? 0),
            'driversDues' => (float) ($office->driversDues ?? 0),
        ];
    }

    /**
     * Subscription state — or the honest answer that this country does not
     * charge subscriptions at all (commission regions bill per ride instead).
     */
    private function subscription(int $officeId): array
    {
        if (! RegionBilling::isSubscription()) {
            return ['mode' => 'commission', 'row' => null];
        }

        try {
            $row = OfficeSubscription::on(TenantConnection::current())
                ->where('office_id', $officeId)
                ->orderByDesc('id')
                ->first();
        } catch (Throwable $e) {
            $row = null;
        }

        return ['mode' => 'subscription', 'row' => $row];
    }
}
