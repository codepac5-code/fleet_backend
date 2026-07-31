<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Subscription\OfficeSubscriptionService;
use App\Http\Core\Classes\Subscription\SubscriptionBillingService;
use App\Http\Core\Const\Subscription\PlanKey;
use App\Http\Core\Const\Subscription\SubscriptionStatus;
use App\Models\OfficeSubServicePrice;
use App\Models\SubscriptionPlan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Three holes this covers, all found by tracing "when does a new office pay?":
 * the free trial was never actually started, nothing ever ended it, and an
 * office with no subscription kept being offered to riders for free.
 */
class OfficeEntitlementTest extends FleetTestCase
{
    protected array $globalMigrations = [
        '2026_06_25_000001_create_subscription_plans_table.php',
    ];

    protected array $tenantMigrations = [
        '2026_06_25_000002_create_office_subscriptions_table.php',
        '2026_07_13_000006_add_billing_lifecycle_to_office_subscriptions.php',
        '2026_01_03_025343_create_office_sub_service_prices_table.php',
        '2026_07_29_000001_add_enabled_to_office_sub_service_prices.php',
    ];

    private OfficeSubscriptionService $subscriptions;

    protected function setUp(): void
    {
        parent::setUp();

        // The price table points at `offices` and `sub_services`; neither carries
        // any meaning for what is under test here, so the rows stand alone.
        DB::connection('fleet_test')->statement('PRAGMA foreign_keys = OFF');

        $previous = DB::getDefaultConnection();
        DB::setDefaultConnection('global');

        foreach (PlanKey::CATALOG as $key => $plan) {
            SubscriptionPlan::query()->create([
                'key' => $key, 'name' => $plan['name'], 'price_minor' => $plan['price_minor'],
                'fleet_commission_rate' => $plan['fleet_rate'], 'driver_limit' => $plan['driver_limit'],
                'is_active' => true, 'sort' => $plan['sort'],
            ]);
        }

        DB::setDefaultConnection($previous);

        $this->subscriptions = new OfficeSubscriptionService();
    }

    public function test_a_trial_can_be_started_and_carries_an_end_date(): void
    {
        $subscription = $this->subscriptions->startTrial(3, PlanKey::BUSINESS, 'USD');

        $this->assertSame(SubscriptionStatus::TRIALING, $subscription->status);
        $this->assertNotNull($subscription->trial_ends_at, 'a trial without an end date can never expire');
        $this->assertTrue($subscription->trial_ends_at->isFuture());
    }

    public function test_a_trial_is_offered_only_once(): void
    {
        $this->assertFalse($this->subscriptions->hasUsedTrial(3));

        $this->subscriptions->startTrial(3, PlanKey::BUSINESS, 'USD');

        $this->assertTrue($this->subscriptions->hasUsedTrial(3), 'a second free trial must not be on offer');
    }

    public function test_paying_during_a_trial_carries_only_the_days_that_are_left(): void
    {
        $billing = new SubscriptionBillingService($this->subscriptions);

        // Nobody has subscribed yet: the plan's own trial is on offer.
        $this->assertSame(14, $billing->checkoutTrialDays(3, PlanKey::BUSINESS));

        $subscription = $this->subscriptions->startTrial(3, PlanKey::BUSINESS, 'USD');
        $subscription->trial_ends_at = Carbon::now()->addDays(5);
        $subscription->save();

        // Mid-trial checkout must NOT restart the clock — handing Stripe the
        // full 14 days again would be a second free trial.
        $this->assertSame(5, $billing->checkoutTrialDays(3, PlanKey::BUSINESS));

        $subscription->trial_ends_at = Carbon::now()->subDay();
        $subscription->save();

        // Trial spent: pay today.
        $this->assertSame(0, $billing->checkoutTrialDays(3, PlanKey::BUSINESS));
    }

    public function test_an_entitled_status_covers_trialing_active_and_past_due(): void
    {
        // past_due is deliberately entitled: an office chasing a failed payment
        // keeps trading while it sorts the card out.
        $this->assertContains(SubscriptionStatus::TRIALING, SubscriptionStatus::ENTITLED);
        $this->assertContains(SubscriptionStatus::ACTIVE, SubscriptionStatus::ENTITLED);
        $this->assertContains(SubscriptionStatus::PAST_DUE, SubscriptionStatus::ENTITLED);
        $this->assertNotContains(SubscriptionStatus::CANCELED, SubscriptionStatus::ENTITLED);
        $this->assertNotContains(SubscriptionStatus::ENDED, SubscriptionStatus::ENTITLED);
    }

    public function test_offering_a_sub_service_is_separate_from_pricing_it(): void
    {
        $offered = OfficeSubServicePrice::query()->create([
            'office_id' => 1, 'sub_service_id' => 7,
            'openPrice' => 0, 'kmPrice' => 0, 'minutePrice' => 0, 'is_enabled' => true,
        ]);

        $priced = OfficeSubServicePrice::query()->create([
            'office_id' => 2, 'sub_service_id' => 7,
            'openPrice' => 8, 'kmPrice' => 2.5, 'minutePrice' => 0.4, 'is_enabled' => true,
        ]);

        // Enabled with no rates means "offer it at the catalog price" — treating
        // that as an override would charge the rider zero.
        $this->assertFalse($offered->isPriceOverride());
        $this->assertTrue($priced->isPriceOverride());
    }

    public function test_a_disabled_row_is_not_offered_but_keeps_its_price(): void
    {
        $row = OfficeSubServicePrice::query()->create([
            'office_id' => 1, 'sub_service_id' => 9,
            'openPrice' => 12, 'kmPrice' => 3, 'minutePrice' => 1, 'is_enabled' => false,
        ]);

        $this->assertSame(0, OfficeSubServicePrice::query()->offered()->where('office_id', 1)->count());
        $this->assertSame(12.0, (float) $row->openPrice, 'turning a service off must not lose the price behind it');
    }
}
