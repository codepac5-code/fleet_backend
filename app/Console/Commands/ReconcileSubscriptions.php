<?php

namespace App\Console\Commands;

use App\Http\Core\Classes\Billing\RegionBilling;
use App\Http\Core\Classes\Subscription\SubscriptionCheckoutReconciler;
use App\Http\Core\GeoServices\ShardRunner;
use Illuminate\Console\Command;

/**
 * Pulls what Stripe knows back into the ledger of who is subscribed.
 *
 * The webhook is the normal path, but a missed delivery leaves an office that
 * paid looking unsubscribed — with no trace to work from, because the checkout
 * session id is only ever in a redirect URL. Stripe keeps `office_id` and
 * `plan_key` on every subscription we create, so this rebuilds the records from
 * the payment provider itself. Safe to run at any time.
 */
class ReconcileSubscriptions extends Command
{
    protected $signature = 'fleet:subscriptions-reconcile {--dry-run : report what would change without writing}';

    protected $description = 'Rebuild office subscriptions from Stripe, for payments whose webhook never arrived.';

    public function __construct(private SubscriptionCheckoutReconciler $reconciler)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        if (! config('services.stripe.secret')) {
            $this->warn('Stripe is not configured; nothing to reconcile.');

            return self::SUCCESS;
        }

        ShardRunner::eachCountry(function ($node) {
            $country = strtoupper((string) ($node->country_code ?? ''));

            if (! RegionBilling::isSubscription($node)) {
                return;
            }

            if ($this->option('dry-run')) {
                $this->line(sprintf('[dry-run] [%s] would reconcile from Stripe', $country));

                return;
            }

            $result = $this->reconciler->syncCountry($country);

            if ($result['error'] !== null) {
                $this->warn(sprintf('[%s] %s', $country, $result['error']));

                return;
            }

            $this->line(sprintf('[%s] reconciled: %d, skipped: %d', $country, $result['applied'], $result['skipped']));
        });

        return self::SUCCESS;
    }
}
