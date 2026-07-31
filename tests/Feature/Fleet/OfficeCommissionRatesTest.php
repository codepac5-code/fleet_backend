<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Ledger\FleetWalletService;
use App\Http\Core\Classes\Subscription\CommissionResolver;
use App\Http\Core\Const\Subscription\CommissionDefaults;
use App\Models\Office;
use Illuminate\Support\Facades\DB;

/**
 * Who takes what out of a fare.
 *
 * The operator's model is 5 / 95: five percent to the platform, and the rest to
 * the office and its driver together, with the OFFICE deciding how that rest is
 * divided. Neither half of that was expressible. The fleet's cut came only from
 * the office's subscription plan — 18% on the free plan — so in a commission
 * country every office paid the same rate and the platform could not agree a
 * different one with a particular office. And the office's own cut was
 * hard-zero, so unless somebody set a per-driver override by hand the office
 * earned nothing on its drivers' rides.
 */
class OfficeCommissionRatesTest extends FleetTestCase
{
    protected array $tenantMigrations = [
        '2024_10_29_211028_create_offices_table.php',
        '2026_07_31_000003_add_commission_rates_to_offices.php',
        '2026_06_25_000002_create_office_subscriptions_table.php',
        '2026_07_13_000006_add_billing_lifecycle_to_office_subscriptions.php',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        DB::connection('fleet_test')->statement('PRAGMA foreign_keys = OFF');
    }

    private int $seq = 0;

    private function office(?float $fleet = null, ?float $driver = null): Office
    {
        $this->seq++;

        return Office::query()->create([
            'officeName' => 'Damascus Luxury Fleet',
            'email' => 'damascusluxury+' . $this->seq . '@fleet.plus',
            'password' => 'secret123',
            'status' => 1,
            'fleet_commission_rate' => $fleet,
            'driver_commission_rate' => $driver,
        ]);
    }

    private function rates(int $officeId): array
    {
        return app(CommissionResolver::class)->forOffice($officeId);
    }

    public function test_an_office_nobody_negotiated_with_pays_five_percent(): void
    {
        $rates = $this->rates((int) $this->office()->id);

        $this->assertSame(5.0, $rates['fleet_rate']);
        // The other 95 is the office's and the driver's; taking none of it, the
        // driver keeps all 95.
        $this->assertSame(0.0, $rates['office_rate']);
    }

    public function test_the_platform_can_agree_a_different_rate_with_one_office(): void
    {
        $special = $this->office(fleet: 2.5);
        $ordinary = $this->office(fleet: null);

        $this->assertSame(2.5, $this->rates((int) $special->id)['fleet_rate']);
        $this->assertSame(5.0, $this->rates((int) $ordinary->id)['fleet_rate'], 'one office\'s deal must not move anybody else');
    }

    public function test_the_office_decides_what_it_takes_from_its_drivers(): void
    {
        $rates = $this->rates((int) $this->office(driver: 20.0)->id);

        $this->assertSame(5.0, $rates['fleet_rate']);
        $this->assertSame(20.0, $rates['office_rate']);
    }

    public function test_an_office_cannot_take_more_than_the_platform_left_behind(): void
    {
        // 10 to the fleet leaves 90; a 95% office cut would settle the ride by
        // taking money the driver never earned.
        $rates = $this->rates((int) $this->office(fleet: 10.0, driver: 95.0)->id);

        $this->assertSame(90.0, $rates['office_rate']);
        $this->assertSame(0.0, round(100 - $rates['fleet_rate'] - $rates['office_rate'], 2));
    }

    public function test_a_cleared_rate_follows_the_platform_again_rather_than_freezing(): void
    {
        $office = $this->office(fleet: 12.0);

        $office->fleet_commission_rate = null;
        $office->save();

        $this->assertSame(CommissionDefaults::FLEET_RATE, $this->rates((int) $office->id)['fleet_rate']);
    }

    public function test_the_default_split_pays_out_five_ninetyfive_on_a_real_fare(): void
    {
        $rates = $this->rates((int) $this->office(driver: 20.0)->id);

        $split = app(FleetWalletService::class)->splitThreeWay(10000, $rates['fleet_rate'], $rates['office_rate']);

        $this->assertSame(500, $split['fleet']);
        $this->assertSame(2000, $split['office']);
        $this->assertSame(7500, $split['driver']);
        $this->assertSame(10000, $split['fleet'] + $split['office'] + $split['driver']);
    }
}
