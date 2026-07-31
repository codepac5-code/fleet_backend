<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Incentive\IncentiveService;
use App\Http\Core\Classes\Ledger\FleetWalletService;
use App\Http\Core\Classes\Ledger\LedgerService;
use App\Models\DriverIncentive;
use App\Models\DriverIncentiveProgress;

class DriverIncentiveTest extends FleetTestCase
{
    protected array $tenantMigrations = [
        '2026_06_24_000001_create_ledger_accounts_table.php',
        '2026_06_24_000002_create_ledger_transactions_table.php',
        '2026_06_24_000003_create_ledger_entries_table.php',
        '2026_07_28_000009_create_driver_incentives_tables.php',
    ];

    private IncentiveService $incentives;
    private FleetWalletService $wallet;

    protected function setUp(): void
    {
        parent::setUp();

        $this->wallet = new FleetWalletService(new LedgerService());
        $this->incentives = new IncentiveService($this->wallet);
    }

    private function rule(int $target, int $rewardMinor, string $window = 'week', bool $active = true): DriverIncentive
    {
        return DriverIncentive::query()->create([
            'name_en' => $target . ' rides', 'name_ar' => $target . ' رحلات',
            'window' => $window, 'target_rides' => $target,
            'reward_minor' => $rewardMinor, 'is_active' => $active,
        ]);
    }

    private function drive(int $driverId, int $times): void
    {
        for ($i = 1; $i <= $times; $i++) {
            $this->incentives->recordRide($driverId, 5000 + $i);
        }
    }

    public function test_reward_lands_exactly_when_the_target_is_hit(): void
    {
        $this->rule(3, 5000);

        $this->drive(7, 2);
        $this->assertSame(0, $this->wallet->walletBalanceMinor('driver', 7, 'USD'), 'two of three rides earns nothing yet');

        $this->drive(7, 1);
        $this->assertSame(5000, $this->wallet->walletBalanceMinor('driver', 7, 'USD'));
    }

    public function test_further_rides_in_the_same_window_do_not_pay_again(): void
    {
        $this->rule(2, 5000);

        $this->drive(7, 6);

        $this->assertSame(5000, $this->wallet->walletBalanceMinor('driver', 7, 'USD'));
        $this->assertSame(1, DriverIncentiveProgress::query()->where('driver_id', 7)->where('rewarded', true)->count());
    }

    public function test_the_reward_comes_out_of_fleet_revenue(): void
    {
        $this->rule(1, 2500);

        $this->drive(7, 1);

        $this->assertSame(-2500, $this->wallet->revenueBalanceMinor('fleet', 0, 'USD'));
    }

    public function test_a_paused_rule_neither_counts_nor_pays(): void
    {
        $this->rule(1, 5000, 'week', false);

        $this->drive(7, 3);

        $this->assertSame(0, $this->wallet->walletBalanceMinor('driver', 7, 'USD'));
        $this->assertSame(0, DriverIncentiveProgress::query()->count());
    }

    public function test_several_rules_advance_from_the_same_ride(): void
    {
        $this->rule(1, 1000, 'day');
        $this->rule(2, 4000, 'week');

        $this->drive(7, 2);

        $this->assertSame(5000, $this->wallet->walletBalanceMinor('driver', 7, 'USD'), 'both the daily and the weekly target paid');
    }

    public function test_progress_is_reported_per_driver(): void
    {
        $this->rule(5, 9000);

        $this->drive(7, 2);
        $progress = $this->incentives->progressFor(7);

        $this->assertCount(1, $progress);
        $this->assertSame(2, $progress[0]['rides']);
        $this->assertSame(3, $progress[0]['remaining']);
        $this->assertFalse($progress[0]['rewarded']);
        $this->assertSame(0, $this->incentives->progressFor(8)[0]['rides'], 'another driver starts at zero');
    }

    public function test_windows_produce_distinct_periods(): void
    {
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $this->incentives->period('day'));
        $this->assertMatchesRegularExpression('/^\d{4}-W\d{2}$/', $this->incentives->period('week'));
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}$/', $this->incentives->period('month'));
    }
}
