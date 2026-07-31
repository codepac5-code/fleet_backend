<?php

namespace App\Http\Core\Classes\Subscription;

use App\Http\Core\Classes\Settings\AppSettings;
use App\Http\Core\Const\Subscription\CommissionDefaults;
use App\Http\Core\Const\Subscription\PlanKey;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\Driver;
use App\Models\Office;
use Throwable;

class CommissionResolver
{
    public function __construct(private OfficeSubscriptionService $subscriptions)
    {
    }

    public function forOfficeBooking(int $officeId): array
    {
        $rates = $this->forOffice($officeId);

        $override = AppSettings::float('office_booking_fleet_rate', -1.0);

        if ($override >= 0) {
            $rates['fleet_rate'] = $override;
        }

        return $rates;
    }

    /**
     * A driver's negotiated office rate, or null when they follow the office's.
     * Best-effort: a shard whose drivers table predates the column, or no driver
     * at all, simply means "no override".
     */
    public function driverOverride(int $driverId): ?float
    {
        if ($driverId <= 0) {
            return null;
        }

        try {
            // Drivers live in the COUNTRY database and ids repeat across them —
            // a bare query would read the platform database and could hand back
            // another country's driver with the same id.
            $override = Driver::on(TenantConnection::current())->whereKey($driverId)->value('commission_rate_override');

            return $override !== null ? (float) $override : null;
        } catch (Throwable $e) {
            return null;
        }
    }

    /**
     * The three-way split for an office, most specific source first.
     *
     * The fleet's cut used to come ONLY from the subscription plan, so in a
     * commission country every office paid the same rate and the platform could
     * not agree a different one with a particular office. And the office's own
     * cut was hard-zero, so unless somebody set a per-driver override by hand
     * the office earned nothing on its drivers' rides.
     *
     *   fleet  — the office's negotiated rate, else its plan's, else the
     *            platform default (5%).
     *   office — what the office takes out of the rest; the driver keeps the
     *            remainder. A per-driver override replaces this downstream in
     *            {@see \App\Http\Core\Classes\Ledger\BookingSettlementService}.
     */
    public function forOffice(int $officeId): array
    {
        $office = $this->office($officeId);
        $subscription = $this->subscriptions->currentFor($officeId);

        $fleetRate = $office?->fleet_commission_rate;

        if ($fleetRate === null) {
            $fleetRate = $subscription?->fleet_commission_rate ?? CommissionDefaults::fleetRate();
        }

        $officeRate = $office?->driver_commission_rate
            ?? $subscription?->office_commission_rate
            ?? PlanKey::DEFAULT_OFFICE_RATE;

        return [
            'fleet_rate' => CommissionDefaults::clamp((float) $fleetRate),
            // The office cannot take a share that leaves the driver in debt —
            // the two cuts together are capped at the whole fare.
            'office_rate' => CommissionDefaults::clamp(min((float) $officeRate, 100.0 - CommissionDefaults::clamp((float) $fleetRate))),
            'subscription_plan' => $subscription?->plan_key ?? PlanKey::FREE,
        ];
    }

    /**
     * Best-effort: a shard whose offices table predates the columns, or no
     * office at all, simply means "no negotiated rate".
     */
    private function office(int $officeId): ?Office
    {
        if ($officeId <= 0) {
            return null;
        }

        try {
            return Office::on(TenantConnection::current())->find($officeId);
        } catch (Throwable $e) {
            return null;
        }
    }
}
