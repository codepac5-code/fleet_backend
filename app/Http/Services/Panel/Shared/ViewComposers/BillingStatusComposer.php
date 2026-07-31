<?php

namespace App\Http\Services\Panel\Shared\ViewComposers;

use App\Http\Core\Classes\Billing\RegionBilling;
use App\Http\Core\Classes\Subscription\OfficeSubscriptionService;
use App\Http\Core\Const\Subscription\SubscriptionStatus;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\View\View;
use Throwable;

/**
 * The office's billing state, on every page.
 *
 * A trial that quietly runs out is the worst way for an office to learn it has
 * one: the only place that said anything was the subscription screen, which
 * nobody opens until something has already stopped working.
 */
class BillingStatusComposer
{
    public function __construct(private EntityScope $scope, private OfficeSubscriptionService $subscriptions) {}

    public function compose(View $view): void
    {
        $view->with('panelBilling', $this->state());
    }

    private function state(): ?array
    {
        $officeId = (int) $this->scope->officeId();

        if ($officeId <= 0 || ! RegionBilling::isSubscription()) {
            return null;
        }

        try {
            $subscription = $this->subscriptions->currentFor($officeId);
        } catch (Throwable $e) {
            return null;
        }

        if ($subscription === null) {
            return ['tone' => 'danger', 'status' => 'none', 'days' => null];
        }

        if ($subscription->status === SubscriptionStatus::PAST_DUE) {
            return ['tone' => 'danger', 'status' => SubscriptionStatus::PAST_DUE, 'days' => null];
        }

        if ($subscription->status !== SubscriptionStatus::TRIALING) {
            return null;
        }

        $days = $this->subscriptions->remainingTrialDays($officeId);

        return [
            'tone' => $days <= 3 ? 'danger' : 'trial',
            'status' => SubscriptionStatus::TRIALING,
            'days' => $days,
        ];
    }
}
