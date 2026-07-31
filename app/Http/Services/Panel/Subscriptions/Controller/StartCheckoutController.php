<?php

namespace App\Http\Services\Panel\Subscriptions\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Billing\RegionBilling;
use App\Http\Core\Classes\Subscription\SubscriptionBillingService;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class StartCheckoutController extends Controller
{
    public function __invoke(Request $request, EntityScope $scope, SubscriptionBillingService $billing): RedirectResponse
    {
        if (!RegionBilling::isSubscription()) {
            return back()->with('error', textByLanguage('الاشتراك غير متاح في منطقتك.', 'Subscriptions are not available in your region.'));
        }

        $data = $request->validate([
            'plan_key' => ['required', 'string', 'max:32'],
            // "now" = end the trial and start paying today; anything else keeps
            // the remaining trial days and bills when they run out.
            'billing_starts' => ['nullable', 'in:now,after_trial'],
        ]);

        $back = route('panel.' . $scope->guard() . '.subscription.show');
        $email = optional($scope->user())->email;

        try {
            $url = $billing->createCheckoutSession(
                (int) $scope->officeId(),
                $data['plan_key'],
                // Stripe fills the placeholder in, and the id lets the return
                // trip activate the plan itself when no webhook arrives.
                $back . '?checkout=success&session_id={CHECKOUT_SESSION_ID}',
                $back . '?checkout=cancel',
                $email !== null ? (string) $email : null,
                ($data['billing_starts'] ?? null) === 'now'
            );
        } catch (Throwable $e) {
            return back()->with('error', textByLanguage('تعذّر بدء الدفع: ', 'Could not start checkout: ') . $e->getMessage());
        }

        return redirect()->away($url);
    }
}
