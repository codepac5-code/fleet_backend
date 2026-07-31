<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Billing\RegionBilling;
use App\Http\Core\Classes\Subscription\CommissionResolver;
use App\Http\Core\Classes\Subscription\OfficeSubscriptionService;
use App\Http\Core\Const\Billing\BillingMode;
use App\Http\Core\Const\Subscription\PlanKey;
use App\Http\Core\Const\Subscription\SubscriptionStatus;
use App\Models\InfrastructureNode;
use App\Models\OfficeSubscription;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Facades\DB;

class OfficeSubscriptionLifecycleTest extends FleetTestCase
{
    protected array $globalMigrations = [
        '2026_06_25_000001_create_subscription_plans_table.php',
        '2026_07_13_000005_add_trial_days_to_subscription_plans.php',
    ];

    protected array $tenantMigrations = [
        '2026_06_25_000002_create_office_subscriptions_table.php',
        '2026_07_13_000006_add_billing_lifecycle_to_office_subscriptions.php',
    ];

    private OfficeSubscriptionService $subs;
    private CommissionResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $prev = DB::getDefaultConnection();
        DB::setDefaultConnection('global');
        foreach (PlanKey::CATALOG as $key => $p) {
            SubscriptionPlan::query()->create([
                'key' => $key, 'name' => $p['name'], 'price_minor' => $p['price_minor'],
                'fleet_commission_rate' => $p['fleet_rate'], 'driver_limit' => $p['driver_limit'],
                'is_active' => true, 'sort' => $p['sort'],
            ]);
        }
        DB::setDefaultConnection($prev);

        $this->subs = new OfficeSubscriptionService();
        $this->resolver = new CommissionResolver($this->subs);
    }

    public function test_start_trial_creates_trialing_subscription(): void
    {
        $sub = $this->subs->startTrial(10, PlanKey::BUSINESS, 'USD');

        $this->assertSame(SubscriptionStatus::TRIALING, $sub->status);
        $this->assertSame('business', $sub->plan_key);
        $this->assertSame(12.0, (float) $sub->fleet_commission_rate);
        $this->assertNotNull($sub->trial_ends_at);
        $this->assertTrue($sub->trial_ends_at->greaterThan(now()->addDays(13)));
        $this->assertTrue($sub->trial_ends_at->lessThan(now()->addDays(15)));
    }

    public function test_commission_during_trial_uses_plan_rates(): void
    {
        $this->subs->startTrial(11, PlanKey::BUSINESS, 'USD');

        $rates = $this->resolver->forOffice(11);

        $this->assertSame('business', $rates['subscription_plan']);
        $this->assertSame(12.0, $rates['fleet_rate']);
        $this->assertSame(0.0, $rates['office_rate']);
    }

    public function test_per_plan_trial_days_override_default(): void
    {
        $prev = DB::getDefaultConnection();
        DB::setDefaultConnection('global');
        SubscriptionPlan::query()->where('key', PlanKey::SCALE)->update(['trial_days' => 30]);
        DB::setDefaultConnection($prev);

        $sub = $this->subs->startTrial(12, PlanKey::SCALE, 'USD');

        $this->assertTrue($sub->trial_ends_at->greaterThan(now()->addDays(29)));
        $this->assertTrue($sub->trial_ends_at->lessThan(now()->addDays(31)));
    }

    public function test_past_due_still_grants_plan_rates(): void
    {
        $sub = $this->subs->startTrial(13, PlanKey::STARTER, 'USD');
        $sub->status = SubscriptionStatus::PAST_DUE;
        $sub->save();

        $rates = $this->resolver->forOffice(13);

        $this->assertSame('starter', $rates['subscription_plan']);
        $this->assertSame(13.0, $rates['fleet_rate']);
    }

    public function test_canceled_subscription_falls_back_to_free(): void
    {
        $sub = $this->subs->startTrial(14, PlanKey::BUSINESS, 'USD');
        $sub->status = SubscriptionStatus::CANCELED;
        $sub->save();

        $rates = $this->resolver->forOffice(14);

        $this->assertSame('free', $rates['subscription_plan']);
        $this->assertSame(5.0, $rates['fleet_rate']);
    }

    public function test_new_trial_supersedes_existing_current_subscription(): void
    {
        $this->subs->startTrial(15, PlanKey::STARTER, 'USD');
        $this->subs->startTrial(15, PlanKey::BUSINESS, 'USD');

        $entitled = OfficeSubscription::query()
            ->where('office_id', 15)
            ->whereIn('status', SubscriptionStatus::ENTITLED)
            ->get();

        $this->assertCount(1, $entitled);
        $this->assertSame('business', $entitled->first()->plan_key);
    }

    public function test_region_billing_defaults_to_commission_without_node(): void
    {
        $this->assertSame(BillingMode::COMMISSION, RegionBilling::mode());
        $this->assertTrue(RegionBilling::isCommission());
        $this->assertFalse(RegionBilling::isSubscription());
    }

    public function test_region_billing_reads_node_mode(): void
    {
        $subscription = new InfrastructureNode(['billing_mode' => BillingMode::SUBSCRIPTION]);
        $this->assertTrue(RegionBilling::isSubscription($subscription));

        $commission = new InfrastructureNode(['billing_mode' => BillingMode::COMMISSION]);
        $this->assertFalse(RegionBilling::isSubscription($commission));

        $legacy = new InfrastructureNode([]);
        $this->assertTrue(RegionBilling::isCommission($legacy));
    }
}
