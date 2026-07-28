<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Ledger\DriverDebtPolicy;
use App\Http\Core\Classes\Ledger\FleetWalletService;
use App\Http\Core\Classes\Ledger\LedgerService;
use App\Http\Core\Classes\Settings\AppSettings;

class DriverDebtPolicyTest extends FleetTestCase
{
    protected array $tenantMigrations = [
        '2026_06_24_000001_create_ledger_accounts_table.php',
        '2026_06_24_000002_create_ledger_transactions_table.php',
        '2026_06_24_000003_create_ledger_entries_table.php',
        '2026_06_24_000004_create_commission_snapshots_table.php',
    ];

    private FleetWalletService $wallet;
    private DriverDebtPolicy $policy;
    private string $cur = 'USD';

    protected function setUp(): void
    {
        parent::setUp();
        $this->wallet = new FleetWalletService(new LedgerService());
        $this->policy = new DriverDebtPolicy($this->wallet);
        $this->setSettings([]);
    }

    protected function tearDown(): void
    {
        $this->setSettings([]); // don't leak the static cache into other tests
        parent::tearDown();
    }

    /** Prime AppSettings' private static cache directly (its backing store is global + cached). */
    private function setSettings(array $values): void
    {
        $ref = new \ReflectionClass(AppSettings::class);
        $cache = $ref->getProperty('cache');
        $cache->setAccessible(true);
        $cache->setValue(null, $values);
        $available = $ref->getProperty('available');
        $available->setAccessible(true);
        $available->setValue(null, true);
    }

    /** Give the driver dues by charging a cash commission against an empty wallet. */
    private function giveDues(int $driverId, int $fareMinor, int $bookingId): void
    {
        $this->wallet->chargeCommission([
            'booking_id' => $bookingId, 'driver_id' => $driverId, 'office_id' => 0,
            'currency_code' => $this->cur, 'fare_minor' => $fareMinor, 'fleet_rate' => 100.0,
        ]);
    }

    public function test_no_ceiling_configured_never_blocks(): void
    {
        $this->giveDues(9, 10000, 1); // 10000 dues
        $this->assertSame(10000, $this->policy->outstandingMinor(9, $this->cur));
        $this->assertFalse($this->policy->isBlocked(9, $this->cur)); // default ceiling 0 = disabled
    }

    public function test_blocks_when_dues_reach_ceiling(): void
    {
        $this->setSettings(['driver_debt_ceiling_minor' => '5000']);

        $this->giveDues(9, 4000, 1); // 4000 < 5000
        $this->assertFalse($this->policy->isBlocked(9, $this->cur));

        $this->giveDues(9, 1000, 2); // now 5000 >= 5000
        $this->assertTrue($this->policy->isBlocked(9, $this->cur));
    }

    public function test_per_currency_override_takes_precedence(): void
    {
        $this->setSettings([
            'driver_debt_ceiling_minor' => '999999',
            'driver_debt_ceiling_minor_USD' => '3000',
        ]);

        $this->giveDues(9, 3500, 1); // over the USD-specific 3000, under the global fallback
        $this->assertTrue($this->policy->isBlocked(9, $this->cur));
    }
}
