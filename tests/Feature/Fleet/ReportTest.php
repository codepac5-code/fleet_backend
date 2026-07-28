<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Ledger\LedgerService;
use App\Http\Core\Classes\Report\ReportService;
use App\Models\CommissionSnapshot;

class ReportTest extends FleetTestCase
{
    protected array $tenantMigrations = [
        '2026_06_24_000001_create_ledger_accounts_table.php',
        '2026_06_24_000002_create_ledger_transactions_table.php',
        '2026_06_24_000003_create_ledger_entries_table.php',
        '2026_06_24_000004_create_commission_snapshots_table.php',
    ];

    private ReportService $reports;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reports = new ReportService(new LedgerService());
    }

    private function snapshot(int $office, int $driver, int $total, int $fleet, int $officeMinor, int $driverMinor): void
    {
        CommissionSnapshot::query()->create([
            'booking_id' => random_int(1, 1_000_000),
            'office_id' => $office,
            'driver_id' => $driver,
            'currency_code' => 'USD',
            'pricing_style' => 'meter',
            'fare_minor' => $total,
            'discount_minor' => 0,
            'total_minor' => $total,
            'fleet_rate' => 12.0,
            'office_rate' => 18.0,
            'fleet_minor' => $fleet,
            'office_minor' => $officeMinor,
            'driver_minor' => $driverMinor,
            'subscription_plan' => 'business',
        ]);
    }

    public function test_office_and_fleet_and_driver_summaries(): void
    {
        $this->snapshot(3, 9, 10000, 1200, 1800, 7000);
        $this->snapshot(3, 9, 5000, 600, 900, 3500);
        $this->snapshot(4, 11, 8000, 960, 1440, 5600);

        $office = $this->reports->officeSummary(3, 'USD');
        $this->assertSame(2, $office['rides']);
        $this->assertSame(15000, $office['gross_minor']);
        $this->assertSame(2700, $office['office_earned_minor']);
        $this->assertSame(1800, $office['fleet_commission_minor']);

        $fleet = $this->reports->fleetSummary('USD');
        $this->assertSame(3, $fleet['rides']);
        $this->assertSame(23000, $fleet['gross_minor']);
        $this->assertSame(2760, $fleet['fleet_revenue_minor']);

        $driver = $this->reports->driverEarnings(9, 'USD');
        $this->assertSame(2, $driver['rides']);
        $this->assertSame(10500, $driver['earned_minor']);
    }

    public function test_revenue_balance_is_zero_without_postings(): void
    {
        $this->assertSame(0, $this->reports->officeSummary(3, 'USD')['revenue_balance_minor']);
        $this->assertSame(0, $this->reports->fleetSummary('USD')['revenue_balance_minor']);
    }
}
