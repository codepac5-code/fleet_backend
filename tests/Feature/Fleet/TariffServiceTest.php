<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Pricing\TariffService;
use App\Models\Office;
use App\Models\ServiceTariff;

class TariffServiceTest extends FleetTestCase
{
    protected array $tenantMigrations = [
        '2026_07_01_000002_create_service_tariffs_table.php',
        '2024_10_29_211028_create_offices_table.php',
    ];

    private TariffService $tariffs;

    /// Acts as a signed-in office — the tariff form is scoped to whoever owns
    /// the session, so the controller reads the id from the guard.
    private function office(int $id = 3): self
    {
        $office = new Office();
        $office->id = $id;

        return $this->actingAs($office, 'office');
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->tariffs = new TariffService();
    }

    public function test_upsert_creates_then_updates_one_row(): void
    {
        $this->tariffs->upsertForOffice(3, 'standard', 'USD', 'meter', ['base_minor' => 500, 'per_km_minor' => 200]);
        $this->tariffs->upsertForOffice(3, 'standard', 'USD', 'meter', ['base_minor' => 700, 'per_km_minor' => 250, 'minimum_minor' => 1000]);

        $this->assertSame(1, ServiceTariff::query()->where('office_id', 3)->where('service_class', 'standard')->count());

        $tariff = ServiceTariff::query()->where('office_id', 3)->first();
        $this->assertSame(700, (int) $tariff->base_minor);
        $this->assertSame(1000, (int) $tariff->minimum_minor);
    }

    public function test_list_and_remove(): void
    {
        $this->tariffs->upsertForOffice(3, 'standard', 'USD', 'meter', []);
        $this->tariffs->upsertForOffice(3, 'premium', 'USD', 'fixed', ['fixed_minor' => 5000]);

        $this->assertCount(2, $this->tariffs->forOffice(3));

        $this->tariffs->remove(3, 'premium');

        $this->assertCount(1, $this->tariffs->forOffice(3));
        $this->assertSame('standard', $this->tariffs->forOffice(3)[0]->service_class);
    }

    public function test_negative_rates_are_clamped(): void
    {
        $tariff = $this->tariffs->upsertForOffice(3, 'standard', 'USD', 'meter', ['base_minor' => -50]);

        $this->assertSame(0, (int) $tariff->base_minor);
    }

    /**
     * The dashboard form collects WHOLE currency; storage is minor units.
     *
     * The form was labelled "Base" while the field was `base_minor`, so an
     * office that meant 8000 typed 8000 and every ride priced at 80.00 — a
     * hundredfold error with no warning anywhere.
     */
    public function test_whole_currency_converts_to_minor_units(): void
    {
        $this->assertSame(800000, TariffService::toMinor('8000'));
        $this->assertSame(150050, TariffService::toMinor('1500.50'), 'decimals must survive');
        $this->assertSame(25000, TariffService::toMinor('250'));
        $this->assertSame(0, TariffService::toMinor('0'));
    }

    public function test_rounding_is_to_the_nearest_minor_unit(): void
    {
        // Truncating would quietly shave value off every single fare.
        $this->assertSame(1, TariffService::toMinor('0.005'));
        $this->assertSame(101, TariffService::toMinor('1.008'));
    }

    public function test_explicit_minor_units_win_so_older_callers_keep_working(): void
    {
        $this->assertSame(800000, TariffService::toMinor('8000', '800000'));
        // An empty string is "not supplied", not "zero".
        $this->assertSame(800000, TariffService::toMinor('8000', ''));
    }

    public function test_negative_and_junk_input_cannot_produce_a_negative_price(): void
    {
        $this->assertSame(0, TariffService::toMinor('-50'));
        $this->assertSame(0, TariffService::toMinor('abc'));
        $this->assertSame(0, TariffService::toMinor(null));
    }
}
