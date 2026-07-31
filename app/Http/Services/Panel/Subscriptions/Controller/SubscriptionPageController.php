<?php

namespace App\Http\Services\Panel\Subscriptions\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Billing\RegionBilling;
use App\Http\Core\Classes\Subscription\OfficeSubscriptionService;
use App\Http\Core\Classes\Subscription\PlanOverageService;
use App\Http\Core\Classes\Subscription\PlanUsageService;
use App\Http\Core\Classes\Subscription\SubscriptionCheckoutReconciler;
use App\Http\Core\Const\Subscription\PlanKey;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Models\SubscriptionPlan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Throwable;

class SubscriptionPageController extends Controller
{
    public function __invoke(Request $request, EntityScope $scope, OfficeSubscriptionService $subscriptions, PlanUsageService $usage, PlanOverageService $overage, SubscriptionCheckoutReconciler $reconciler): View
    {
        $officeId = (int) $scope->officeId();
        // A plan carried over from the website's pricing grid (?plan=) so the page
        // opens focused on the plan the office signed up for. Only honoured when
        // the office has no subscription yet.
        $preselected = $request->query('plan');
        $preselected = ($preselected !== null && PlanKey::exists($preselected)) ? $preselected : null;

        // Coming back from a paid checkout: settle it here rather than trusting
        // a webhook to have landed first.
        $reconciled = $this->reconcile($request, $officeId, $reconciler);

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
            // A free trial is a one-time offer, so the page must know whether
            // this office already had one before offering it again.
            'trialUsed'      => $subscriptions->hasUsedTrial($officeId),
            // An office inside a trial must still be able to pay — either today
            // or when the trial runs out — so the page needs to know how much of
            // it is left rather than just that one exists.
            'trialDaysLeft'  => $subscriptions->remainingTrialDays($officeId),
            'preselected'    => $subscription === null ? $preselected : null,
            'reconciled'     => $reconciled,
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

    /**
     * @return string|null 'ok' when the payment was turned into a subscription
     *                     here, 'pending' when Stripe does not call it done yet
     */
    private function reconcile(Request $request, int $officeId, SubscriptionCheckoutReconciler $reconciler): ?string
    {
        $sessionId = (string) $request->query('session_id', '');

        if ($request->query('checkout') !== 'success' || $sessionId === '' || $officeId <= 0) {
            return null;
        }

        try {
            $result = $reconciler->fromSession($sessionId, $officeId);
        } catch (Throwable $e) {
            return 'pending';
        }

        return ($result['handled'] ?? false) === true ? 'ok' : 'pending';
    }
}
