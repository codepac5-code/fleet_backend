<?php

namespace App\Console\Commands;

use App\Http\Core\Classes\Billing\RegionBilling;
use App\Http\Core\Classes\Event\DomainEvent;
use App\Http\Core\Classes\Event\EventBus;
use App\Http\Core\Const\Event\Channel;
use App\Http\Core\Const\Event\EventType;
use App\Http\Core\Const\Subscription\SubscriptionStatus;
use App\Http\Core\GeoServices\ShardRunner;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\OfficeSubscription;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Throwable;

/**
 * Ends trials that have run out.
 *
 * A trial was created with a `trial_ends_at` that nothing ever read: an office
 * stayed "trialing" forever, keeping full entitlement without paying. A lapsed
 * trial with no provider subscription behind it becomes PAST_DUE — the same
 * state Stripe puts a failed renewal in — and the office is told.
 *
 * Offices that DID convert are left alone: their status comes from the payment
 * provider's webhooks, never from this sweep.
 */
class SweepSubscriptions extends Command
{
    protected $signature = 'fleet:subscriptions-sweep {--dry-run : report what would change without writing}';

    protected $description = 'Move expired trials to past_due, per country. Paid subscriptions are untouched.';

    public function __construct(private ?EventBus $events = null)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');

        ShardRunner::eachCountry(function ($node) use ($dry) {
            $country = $node->country_code ?? $node->id;

            // Commission countries never had subscriptions to expire.
            if (! RegionBilling::isSubscription($node)) {
                return;
            }

            try {
                $lapsed = OfficeSubscription::on(TenantConnection::current())
                    ->where('status', SubscriptionStatus::TRIALING)
                    ->whereNotNull('trial_ends_at')
                    ->where('trial_ends_at', '<', Carbon::now())
                    ->whereNull('provider_subscription_id')
                    ->get();
            } catch (Throwable $e) {
                $this->warn(sprintf('[%s] skipped: %s', $country, $e->getMessage()));

                return;
            }

            foreach ($lapsed as $subscription) {
                if (! $dry) {
                    $subscription->status = SubscriptionStatus::PAST_DUE;
                    $subscription->save();
                    $this->announce($subscription);
                }
            }

            $this->line(sprintf('%s[%s] trials ended: %d', $dry ? '[dry-run] ' : '', $country, $lapsed->count()));
        });

        return self::SUCCESS;
    }

    private function announce(OfficeSubscription $subscription): void
    {
        if ($this->events === null) {
            return;
        }

        try {
            $this->events->emit(new DomainEvent(
                EventType::SUBSCRIPTION_PAST_DUE,
                [Channel::office((int) $subscription->office_id), Channel::admin()],
                [
                    'office_id' => (int) $subscription->office_id,
                    'plan_key' => $subscription->plan_key,
                    'reason' => 'trial_expired',
                ]
            ));
        } catch (Throwable $e) {
            // Telling the office is best-effort; the status change is what matters.
        }
    }
}
