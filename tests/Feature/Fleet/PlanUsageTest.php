<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Subscription\PlanUsageService;
use Tests\TestCase;

/**
 * The overage MATH is a pure static — unit-tested here. The full DB path
 * (forOffice: subscription + drivers + rides on the active shard) is gated
 * behind a subscription region and is left to manual/panel verification, since
 * exercising it needs a live `dynamic` shard connection the sqlite harness
 * can't easily stand up. The safety-critical gate (commission region → no
 * limits) IS covered below without any shard.
 */
class PlanUsageTest extends TestCase
{
    public function test_overage_is_zero_when_unlimited(): void
    {
        $this->assertSame(0, PlanUsageService::overage(100, null));
    }

    public function test_overage_is_zero_when_within_limit(): void
    {
        $this->assertSame(0, PlanUsageService::overage(5, 5));
        $this->assertSame(0, PlanUsageService::overage(3, 5));
    }

    public function test_overage_counts_units_over_the_limit(): void
    {
        $this->assertSame(1, PlanUsageService::overage(6, 5));
        $this->assertSame(20, PlanUsageService::overage(520, 500));
    }

    public function test_commission_region_has_no_plan_limits(): void
    {
        // No shard active → RegionBilling defaults to commission → forOffice
        // returns null before touching the DB (limits don't apply off-plan).
        $this->assertNull((new PlanUsageService())->forOffice(5));
        $this->assertFalse((new PlanUsageService())->driverAddWouldExceed(5));
    }
}
