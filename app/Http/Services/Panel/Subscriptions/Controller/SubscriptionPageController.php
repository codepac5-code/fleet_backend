<?php

namespace App\Http\Services\Panel\Subscriptions\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Billing\RegionBilling;
use App\Http\Core\Classes\Subscription\OfficeSubscriptionService;
use App\Http\Core\Classes\Subscription\PlanOverageService;
use App\Http\Core\Classes\Subscription\PlanUsageService;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Models\SubscriptionPlan;
use Illuminate\Contracts\View\View;

class SubscriptionPageController extends Controller
{
    public function __invoke(EntityScope $scope, OfficeSubscriptionService $subscriptions, PlanUsageService $usage, PlanOverageService $overage): View
    {
        $officeId = (int) $scope->officeId();
        $subscription = $subscriptions->currentFor($officeId);

        $plans = SubscriptionPlan::query()
            ->where('is_active', true)
            ->where('price_minor', '>', 0)
            ->orderBy('sort')
            ->get()
            ->map(fn ($p) => [
                'key' => $p->key,
                'name' => $p->name,
                'price_minor' => (int) $p->price_minor,
                'currency_code' => $p->currency_code,
                'fleet_commission_rate' => (float) $p->fleet_commission_rate,
                'trial_days' => $p->trial_days !== null ? (int) $p->trial_days : OfficeSubscriptionService::DEFAULT_TRIAL_DAYS,
                'is_popular' => (bool) $p->is_popular,
                'features' => is_array($p->features) ? $p->features : [],
            ])
            ->all();

        return view('panel.subscription.index', [
            'entity'         => $scope->guard(),
            'mode'           => RegionBilling::mode(),
            'usage'          => $usage->forOffice($officeId),
            'overagePending' => $overage->pendingTotalMinor($officeId),
            'plans'          => $plans,
            'subscription' => $subscription === null ? null : [
                'plan_key'               => $subscription->plan_key,
                'fleet_commission_rate'  => (float) $subscription->fleet_commission_rate,
                'office_commission_rate' => (float) $subscription->office_commission_rate,
                'price_minor'            => (int) $subscription->price_minor,
                'currency_code'          => $subscription->currency_code,
                'status'                 => $subscription->status,
                'trial_ends_at'          => $subscription->trial_ends_at,
                'current_period_end'     => $subscription->current_period_end,
                'cancel_at_period_end'   => (bool) $subscription->cancel_at_period_end,
            ],
        ]);
    }
}
