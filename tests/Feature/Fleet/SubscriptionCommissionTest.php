<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Ledger\BookingSettlementService;
use App\Http\Core\Classes\Ledger\FleetWalletService;
use App\Http\Core\Classes\Ledger\LedgerService;
use App\Http\Core\Classes\Ledger\Money;
use App\Http\Core\Classes\Subscription\CommissionResolver;
use App\Http\Core\Classes\Subscription\OfficeSubscriptionService;
use App\Http\Core\Const\Subscription\PlanKey;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Facades\DB;

class SubscriptionCommissionTest extends FleetTestCase
{
    protected array $globalMigrations = [
        '2026_06_25_000001_create_subscription_plans_table.php',
    ];

    protected array $tenantMigrations = [
        '2026_06_24_000001_create_ledger_accounts_table.php',
        '2026_06_24_000002_create_ledger_transactions_table.php',
        '2026_06_24_000003_create_ledger_entries_table.php',
        '2026_06_24_000004_create_commission_snapshots_table.php',
        '2026_06_25_000002_create_office_subscriptions_table.php',
    ];

    private OfficeSubscriptionService $subs;
    private CommissionResolver $resolver;
    private BookingSettlementService $settle;
    private FleetWalletService $wallet;
    private string $cur = 'USD';

    protected function setUp(): void
    {
        parent::setUp();

        $prev = DB::getDefaultConnection();
        DB::setDefaultConnection('global');
        foreach (PlanKey::CATALOG as $key => $p) {
            SubscriptionPlan::query()->create([
                'key' => $key, 'name' => $p['name'], 'price_minor' => $p['price_minor'],
                'fleet_commission_rate' => $p['fleet_rate'], 'driver_limit' => $p['driver_limit'], 'is_active' => true, 'sort' => $p['sort'],
            ]);
        }
        DB::setDefaultConnection($prev);

        $this->wallet = new FleetWalletService(new LedgerService());
        $this->subs = new OfficeSubscriptionService();
        $this->resolver = new CommissionResolver($this->subs);
        $this->settle = new BookingSettlementService($this->wallet, $this->resolver);
    }

    public function test_no_subscription_falls_back_to_free(): void
    {
        $r = $this->resolver->forOffice(3);
        $this->assertSame('free', $r['subscription_plan']);
        $this->assertSame(18.0, $r['fleet_rate']);
    }

    public function test_subscribe_snapshots_plan_rate_from_global(): void
    {
        $sub = $this->subs->subscribe(3, PlanKey::BUSINESS, 18.0, $this->cur);
        $this->assertSame('business', $sub->plan_key);
        $this->assertSame(12.0, (float) $sub->fleet_commission_rate);

        $r = $this->resolver->forOffice(3);
        $this->assertSame(12.0, $r['fleet_rate']);
        $this->assertSame(18.0, $r['office_rate']);
    }

    public function test_settle_digital_uses_subscription_rates(): void
    {
        $this->subs->subscribe(3, PlanKey::BUSINESS, 18.0, $this->cur);
        $total = Money::toMinor(49.50);
        $this->wallet->topUp(7, $total, $this->cur, 'tu');
        $this->wallet->holdRide(2001, 7, $total, $this->cur, 'hold');

        $this->settle->settleDigital([
            'booking_id' => 2001, 'office_id' => 3, 'driver_id' => 9, 'user_id' => 7,
            'currency_code' => $this->cur, 'total_minor' => $total, 'pricing_style' => 'fixed',
        ]);

        $snap = DB::table('commission_snapshots')->where('booking_id', 2001)->first();
        $this->assertSame('business', $snap->subscription_plan);
        $this->assertSame(594, (int) $snap->fleet_minor);
        $this->assertSame(891, (int) $snap->office_minor);
        $this->assertSame(3465, (int) $snap->driver_minor);
    }

    public function test_resubscribe_keeps_single_active(): void
    {
        $this->subs->subscribe(3, PlanKey::BUSINESS, 18.0, $this->cur);
        $this->subs->subscribe(3, PlanKey::SCALE, 16.0, $this->cur);
        $this->assertSame(11.0, $this->resolver->forOffice(3)['fleet_rate']);
        $this->assertSame(1, (int) DB::table('office_subscriptions')->where('office_id', 3)->where('status', 'active')->count());
    }
}
