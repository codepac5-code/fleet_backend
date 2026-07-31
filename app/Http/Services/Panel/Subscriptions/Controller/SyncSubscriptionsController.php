<?php

namespace App\Http\Services\Panel\Subscriptions\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Subscription\SubscriptionCheckoutReconciler;
use App\Http\Core\GeoServices\ShardManager;
use Illuminate\Http\RedirectResponse;
use Throwable;

/**
 * Pull the current country's subscriptions back from Stripe on demand.
 *
 * The hourly command does this on its own, but when an office says "I paid and
 * it is not showing", nobody wants to be told to wait an hour.
 */
class SyncSubscriptionsController extends Controller
{
    public function __invoke(SubscriptionCheckoutReconciler $reconciler): RedirectResponse
    {
        $country = (string) (optional(ShardManager::current())->country_code ?? '');

        try {
            $result = $reconciler->syncCountry($country);
        } catch (Throwable $e) {
            return back()->with('error', textByLanguage('تعذّرت المزامنة: ', 'Sync failed: ') . $e->getMessage());
        }

        if ($result['error'] !== null) {
            return back()->with('error', textByLanguage('تعذّرت المزامنة: ', 'Sync failed: ') . $result['error']);
        }

        return back()->with('status', textByLanguage(
            'تمّت المزامنة مع Stripe — اشتراكات محدَّثة: ' . $result['applied'],
            'Synced with Stripe — subscriptions updated: ' . $result['applied']
        ));
    }
}
