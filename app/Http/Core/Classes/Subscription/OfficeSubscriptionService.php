<?php

namespace App\Http\Core\Classes\Subscription;

use App\Http\Core\Const\Subscription\PlanKey;
use App\Http\Core\Const\Subscription\SubscriptionStatus;
use App\Models\OfficeSubscription;
use App\Models\SubscriptionPlan;
use Carbon\Carbon;
use RuntimeException;
use Throwable;

class OfficeSubscriptionService
{
    public const DEFAULT_TRIAL_DAYS = 14;

    public function subscribe(int $officeId, string $planKey, float $officeRate, ?string $currency = null, ?float $fleetRateOverride = null): OfficeSubscription
    {
        if (!PlanKey::exists($planKey)) {
            throw new RuntimeException('unknown subscription plan: ' . $planKey);
        }

        $fleetRate = $fleetRateOverride ?? $this->resolvePlanFleetRate($planKey);

        if ($fleetRate === null) {
            throw new RuntimeException('plan ' . $planKey . ' has no fleet commission rate; pass an override');
        }

        if ($officeRate < 0 || $fleetRate < 0 || ($officeRate + $fleetRate) > 100) {
            throw new RuntimeException('invalid commission rates for office ' . $officeId);
        }

        $this->endCurrent($officeId);

        $plan = PlanKey::plan($planKey);

        return OfficeSubscription::query()->create([
            'office_id' => $officeId,
            'plan_key' => $planKey,
            'fleet_commission_rate' => $fleetRate,
            'office_commission_rate' => $officeRate,
            'price_minor' => (int) ($plan['price_minor'] ?? 0),
            'currency_code' => $currency,
            'status' => SubscriptionStatus::ACTIVE,
            'started_at' => now(),
        ]);
    }

    /**
     * Whether this office ever had a trial. A trial is a one-time offer, so the
     * check looks at history — not just the current row, which `endCurrent()`
     * would have closed.
     */
    public function hasUsedTrial(int $officeId): bool
    {
        try {
            return OfficeSubscription::query()
                ->where('office_id', $officeId)
                ->whereNotNull('trial_ends_at')
                ->exists();
        } catch (Throwable $e) {
            return false;
        }
    }

    public function startTrial(int $officeId, string $planKey, ?string $currency = null, ?float $fleetRateOverride = null): OfficeSubscription
    {
        if (!PlanKey::exists($planKey)) {
            throw new RuntimeException('unknown subscription plan: ' . $planKey);
        }

        $fleetRate = $fleetRateOverride ?? $this->resolvePlanFleetRate($planKey);

        if ($fleetRate === null) {
            throw new RuntimeException('plan ' . $planKey . ' has no fleet commission rate; pass an override');
        }

        $this->endCurrent($officeId);

        $plan = PlanKey::plan($planKey);
        $trialEnds = now()->addDays($this->trialDaysFor($planKey));

        return OfficeSubscription::query()->create([
            'office_id' => $officeId,
            'plan_key' => $planKey,
            'fleet_commission_rate' => $fleetRate,
            'office_commission_rate' => PlanKey::DEFAULT_OFFICE_RATE,
            'price_minor' => (int) ($plan['price_minor'] ?? 0),
            'currency_code' => $currency,
            'status' => SubscriptionStatus::TRIALING,
            'started_at' => now(),
            'trial_ends_at' => $trialEnds,
            'current_period_end' => $trialEnds,
            'provider' => 'stripe',
        ]);
    }

    public function beginFromProvider(int $officeId, string $planKey, ?string $currency, ?string $customerId, ?string $subscriptionId): OfficeSubscription
    {
        if (!PlanKey::exists($planKey)) {
            throw new RuntimeException('unknown subscription plan: ' . $planKey);
        }

        if ($subscriptionId !== null && $subscriptionId !== '') {
            $existing = OfficeSubscription::query()
                ->where('provider_subscription_id', $subscriptionId)
                ->orderByDesc('id')
                ->first();

            if ($existing !== null) {
                return $existing;
            }
        }

        $fleetRate = $this->resolvePlanFleetRate($planKey);

        if ($fleetRate === null) {
            throw new RuntimeException('plan ' . $planKey . ' has no fleet commission rate');
        }

        $this->endCurrent($officeId);

        $plan = PlanKey::plan($planKey);
        $trialEnds = now()->addDays($this->trialDaysFor($planKey));

        return OfficeSubscription::query()->create([
            'office_id' => $officeId,
            'plan_key' => $planKey,
            'fleet_commission_rate' => $fleetRate,
            'office_commission_rate' => PlanKey::DEFAULT_OFFICE_RATE,
            'price_minor' => (int) ($plan['price_minor'] ?? 0),
            'currency_code' => $currency,
            'status' => SubscriptionStatus::TRIALING,
            'started_at' => now(),
            'trial_ends_at' => $trialEnds,
            'current_period_end' => $trialEnds,
            'provider' => 'stripe',
            'provider_customer_id' => $customerId,
            'provider_subscription_id' => $subscriptionId,
        ]);
    }

    public function activeFor(int $officeId): ?OfficeSubscription
    {
        return OfficeSubscription::query()
            ->where('office_id', $officeId)
            ->where('status', SubscriptionStatus::ACTIVE)
            ->orderByDesc('id')
            ->first();
    }

    public function currentFor(int $officeId): ?OfficeSubscription
    {
        return OfficeSubscription::query()
            ->where('office_id', $officeId)
            ->whereIn('status', SubscriptionStatus::ENTITLED)
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Whole days left on the office's running trial, 0 when there is none.
     *
     * Checkout used to hand Stripe the plan's FULL trial length no matter what,
     * so an office that had already burned 12 of its 14 days and then paid was
     * handed a brand-new 14-day trial — a second free month, and no way to be
     * charged today even when it asked to be.
     */
    public function remainingTrialDays(int $officeId): int
    {
        $subscription = $this->currentFor($officeId);

        if ($subscription === null || $subscription->status !== SubscriptionStatus::TRIALING || $subscription->trial_ends_at === null) {
            return 0;
        }

        return max(0, (int) ceil(Carbon::now()->floatDiffInDays($subscription->trial_ends_at, false)));
    }

    public function trialDaysFor(string $planKey): int
    {
        $plan = SubscriptionPlan::query()->where('key', $planKey)->first();

        if ($plan && $plan->trial_days !== null) {
            return (int) $plan->trial_days;
        }

        return self::DEFAULT_TRIAL_DAYS;
    }

    private function endCurrent(int $officeId): void
    {
        OfficeSubscription::query()
            ->where('office_id', $officeId)
            ->whereIn('status', SubscriptionStatus::ENTITLED)
            ->update(['status' => SubscriptionStatus::ENDED]);
    }

    private function resolvePlanFleetRate(string $planKey): ?float
    {
        $plan = SubscriptionPlan::query()->where('key', $planKey)->first();

        if ($plan && $plan->fleet_commission_rate !== null) {
            return (float) $plan->fleet_commission_rate;
        }

        return PlanKey::fleetRate($planKey);
    }
}
