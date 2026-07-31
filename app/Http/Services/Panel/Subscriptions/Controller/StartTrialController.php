<?php

namespace App\Http\Services\Panel\Subscriptions\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Audit\AuditLogService;
use App\Http\Core\Classes\Billing\RegionBilling;
use App\Http\Core\Classes\Subscription\OfficeSubscriptionService;
use App\Http\Core\Const\Subscription\PlanKey;
use App\Http\Core\GeoServices\ShardManager;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

/**
 * Starts the free trial the signup flow promises.
 *
 * `startTrial()` existed but nothing ever called it: a new office was told
 * "confirm your plan to start your free trial" and the only button available
 * sent it straight to Stripe checkout. This is that missing step.
 */
class StartTrialController extends Controller
{
    public function __invoke(Request $request, EntityScope $scope, OfficeSubscriptionService $subscriptions, AuditLogService $audit): RedirectResponse
    {
        $data = $request->validate([
            'plan_key' => ['required', 'string'],
        ]);

        if (! RegionBilling::isSubscription()) {
            return back()->with('error', textByLanguage(
                'هذه الدولة تعمل بنظام العمولة ولا تحتاج اشتراكاً.',
                'This country runs on commission and needs no subscription.'
            ));
        }

        if (! PlanKey::exists($data['plan_key'])) {
            return back()->with('error', textByLanguage('خطة غير معروفة.', 'Unknown plan.'));
        }

        $officeId = (int) $scope->officeId();

        // One trial per office, ever — otherwise an office restarts it forever.
        if ($subscriptions->hasUsedTrial($officeId)) {
            return back()->with('error', textByLanguage(
                'استُخدمت التجربة المجانية لهذا المكتب من قبل — أكمل الاشتراك للمتابعة.',
                'This office has already used its free trial — complete the subscription to continue.'
            ));
        }

        try {
            $subscription = $subscriptions->startTrial($officeId, $data['plan_key'], ShardManager::currency());
        } catch (Throwable $e) {
            return back()->with('error', textByLanguage('تعذّر بدء التجربة: ', 'Could not start the trial: ') . $e->getMessage());
        }

        $audit->record('subscription.trial_started', $scope->guard(), $officeId, 'office', $officeId, [
            'plan' => $subscription->plan_key,
            'trial_ends_at' => (string) $subscription->trial_ends_at,
        ], $request->ip());

        return back()->with('status', textByLanguage(
            'بدأت تجربتك المجانية حتى ' . $subscription->trial_ends_at?->format('Y-m-d'),
            'Your free trial runs until ' . $subscription->trial_ends_at?->format('Y-m-d')
        ));
    }
}
