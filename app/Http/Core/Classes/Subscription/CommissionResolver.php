<?php

namespace App\Http\Core\Classes\Subscription;

use App\Http\Core\Classes\Settings\AppSettings;
use App\Http\Core\Const\Subscription\PlanKey;

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

    public function forOffice(int $officeId): array
    {
        $subscription = $this->subscriptions->currentFor($officeId);

        if ($subscription) {
            return [
                'fleet_rate' => (float) $subscription->fleet_commission_rate,
                'office_rate' => (float) $subscription->office_commission_rate,
                'subscription_plan' => $subscription->plan_key,
            ];
        }

        return [
            'fleet_rate' => (float) PlanKey::fleetRate(PlanKey::FREE),
            'office_rate' => PlanKey::DEFAULT_OFFICE_RATE,
            'subscription_plan' => PlanKey::FREE,
        ];
    }
}
