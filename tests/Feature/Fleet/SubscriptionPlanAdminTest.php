<?php

namespace Tests\Feature\Fleet;

use App\Models\Admin;
use App\Models\SubscriptionPlan;

class SubscriptionPlanAdminTest extends FleetTestCase
{
    protected array $globalMigrations = [
        '2026_05_29_220120_create_infrastructure_nodes_table.php',
        '2026_06_25_000001_create_subscription_plans_table.php',
        '2026_07_01_000005_add_is_popular_to_subscription_plans.php',
        '2026_07_13_000005_add_trial_days_to_subscription_plans.php',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $admin = new Admin();
        $admin->id = 1;
        $this->actingAs($admin, 'admin');
    }

    public function test_store_creates_plan_with_price_in_minor(): void
    {
        $this->post('/admin/plans', [
            'key' => 'business', 'name' => 'Business', 'price' => 35, 'currency_code' => 'USD',
            'fleet_commission_rate' => 12, 'driver_limit' => 50, 'sort' => 3, 'is_active' => 1, 'is_popular' => 1,
        ])->assertRedirect();

        $plan = SubscriptionPlan::query()->where('key', 'business')->first();
        $this->assertNotNull($plan);
        $this->assertSame(3500, (int) $plan->price_minor);
        $this->assertTrue((bool) $plan->is_popular);
        $this->assertSame(12.0, (float) $plan->fleet_commission_rate);
    }

    public function test_missing_key_fails_validation(): void
    {
        $this->post('/admin/plans', ['name' => 'X'])->assertSessionHasErrors('key');
        $this->assertSame(0, SubscriptionPlan::query()->count());
    }

    public function test_only_one_plan_stays_popular(): void
    {
        $this->post('/admin/plans', ['key' => 'a', 'name' => 'A', 'is_popular' => 1, 'is_active' => 1]);
        $this->post('/admin/plans', ['key' => 'b', 'name' => 'B', 'is_popular' => 1, 'is_active' => 1]);

        $this->assertSame(1, SubscriptionPlan::query()->where('is_popular', true)->count());
        $this->assertSame('b', SubscriptionPlan::query()->where('is_popular', true)->first()->key);
    }

    public function test_seed_creates_catalog_plans(): void
    {
        $this->post('/admin/plans/seed')->assertRedirect();

        $this->assertSame(5, SubscriptionPlan::query()->count());
        $this->assertTrue((bool) SubscriptionPlan::query()->where('key', 'business')->first()->is_popular);
    }

    public function test_duplicate_key_is_rejected(): void
    {
        $this->post('/admin/plans', ['key' => 'free', 'name' => 'Free', 'is_active' => 1]);
        $this->post('/admin/plans', ['key' => 'free', 'name' => 'Free 2', 'is_active' => 1])->assertSessionHas('error');

        $this->assertSame(1, SubscriptionPlan::query()->where('key', 'free')->count());
    }
}
