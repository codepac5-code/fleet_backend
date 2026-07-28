<?php

namespace App\Http\Core\Classes\Subscription;

use App\Http\Core\Classes\Billing\RegionBilling;
use App\Http\Core\Const\Subscription\SubscriptionStatus;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\Driver;
use App\Models\OfficeSubscription;
use App\Models\RideBooking;
use App\Models\SubscriptionPlan;
use Carbon\Carbon;

/**
 * Reads an office's plan entitlement vs its actual usage (drivers now, rides
 * this calendar month) and derives any overage. Read-only; enforcement/charging
 * is layered on top. Returns null when plan limits don't apply — i.e. a
 * COMMISSION region, or an office with no entitled subscription.
 *
 * Isolation: subscription + drivers + rides are read on the active country
 * shard; only the plan catalog is global.
 */
class PlanUsageService
{
    public function forOffice(int $officeId): ?array
    {
        if (! RegionBilling::isSubscription()) {
            return null;
        }

        $conn = TenantConnection::current();

        $subscription = OfficeSubscription::on($conn)
            ->where('office_id', $officeId)
            ->whereIn('status', SubscriptionStatus::ENTITLED)
            ->orderByDesc('id')
            ->first();

        if ($subscription === null) {
            return null;
        }

        $plan = SubscriptionPlan::query()->where('key', $subscription->plan_key)->first();

        if ($plan === null) {
            return null;
        }

        $driversUsed = Driver::on($conn)->where('officeId', $officeId)->count();

        $ridesUsed = RideBooking::on($conn)
            ->where('office_id', $officeId)
            ->where('status', 'completed')
            ->where('created_at', '>=', Carbon::now()->startOfMonth())
            ->count();

        $driverLimit = $plan->driver_limit !== null ? (int) $plan->driver_limit : null;
        $rideLimit = $plan->ride_limit !== null ? (int) $plan->ride_limit : null;

        return [
            'plan_key' => (string) $plan->key,
            'plan_name' => (string) $plan->name,
            'status' => (string) $subscription->status,
            'driver_limit' => $driverLimit,
            'drivers_used' => $driversUsed,
            'drivers_over' => self::overage($driversUsed, $driverLimit),
            'ride_limit' => $rideLimit,
            'rides_used' => $ridesUsed,
            'rides_over' => self::overage($ridesUsed, $rideLimit),
            'extra_driver_minor' => $plan->extra_driver_minor !== null ? (int) $plan->extra_driver_minor : null,
            'extra_ride_minor' => $plan->extra_ride_minor !== null ? (int) $plan->extra_ride_minor : null,
            'currency' => (string) ($plan->currency_code ?? ''),
        ];
    }

    /** How far `used` is over `limit`; 0 when unlimited (null) or within. */
    public static function overage(int $used, ?int $limit): int
    {
        return $limit === null ? 0 : max(0, $used - $limit);
    }

    /**
     * Would adding one more driver exceed the plan? False when limits don't
     * apply (no plan / commission region / unlimited).
     */
    public function driverAddWouldExceed(int $officeId): bool
    {
        $usage = $this->forOffice($officeId);

        if ($usage === null || $usage['driver_limit'] === null) {
            return false;
        }

        return ($usage['drivers_used'] + 1) > $usage['driver_limit'];
    }

    /** The per-driver overage price for this office, or null if none/unlimited. */
    public function extraDriverCharge(int $officeId): ?int
    {
        $usage = $this->forOffice($officeId);

        return $usage['extra_driver_minor'] ?? null;
    }
}
