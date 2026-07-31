<?php

namespace Tests\Feature\Fleet;

use App\Http\Services\Panel\Services\Logic\PricingRepository;
use App\Models\Office;
use App\Models\OfficeSubServicePrice;
use App\Models\Service;
use App\Models\SubService;
use Illuminate\Support\Facades\DB;

/**
 * An office is set up under one or more main services and may price only the
 * sub-services beneath them.
 *
 * Participation used to be a free-for-all over every sub-service in the
 * country, so a city-taxi office could tick — and price — the airport corridors
 * of a travel service it does not run, and then surface to riders searching for
 * it. The catalogue is now narrowed to the office's own services, and because a
 * form POST is only a map of ids, the same boundary is enforced on save where
 * it cannot be edited away.
 *
 * The assignment is a list on purpose: one company routinely runs the city
 * meter service AND sells airport corridors, and the single column it shipped
 * as forced a choice between them — whichever half it could not name became
 * unpriceable.
 */
class OfficeServiceScopeTest extends FleetTestCase
{
    protected array $tenantMigrations = [
        '2024_10_26_104402_create_services_table.php',
        '2024_10_26_104427_create_sub_services_table.php',
        '2024_10_29_211028_create_offices_table.php',
        '2025_06_21_103445_create_office_services_table.php',
        '2026_01_03_025343_create_office_sub_service_prices_table.php',
        '2026_07_29_000001_add_enabled_to_office_sub_service_prices.php',
    ];

    private int $meterService;
    private int $travelService;
    private int $meterSub;
    private int $travelSub;

    protected function setUp(): void
    {
        parent::setUp();

        DB::connection('fleet_test')->statement('PRAGMA foreign_keys = OFF');

        $meter = Service::query()->create(['image' => 'm.png', 'title' => 'تاكسي المدينة', 'title_en' => 'City Taxi', 'travel_service' => 0, 'status' => 1]);
        $travel = Service::query()->create(['image' => 't.png', 'title' => 'سفر', 'title_en' => 'Travel', 'travel_service' => 1, 'status' => 1]);

        $this->meterService = (int) $meter->id;
        $this->travelService = (int) $travel->id;

        $this->meterSub = (int) SubService::query()->create([
            'name' => 'عادي', 'name_en' => 'Standard', 'serviceId' => $meter->id,
            'status' => 1, 'openPrice' => 5, 'kmPrice' => 2, 'minutePrice' => 1,
        ])->id;

        $this->travelSub = (int) SubService::query()->create([
            'name' => 'استقبال من المطار', 'name_en' => 'Airport Pickup', 'serviceId' => $travel->id,
            'status' => 1, 'openPrice' => 0, 'kmPrice' => 0, 'minutePrice' => 0,
        ])->id;
    }

    private function office(array $serviceIds): Office
    {
        $office = Office::query()->create([
            'officeName' => 'Damascus Luxury Fleet',
            'email' => 'damascusluxury@fleet.plus',
            'password' => 'secret123',
            'status' => 1,
        ]);

        $office->services()->sync($serviceIds);

        return $office;
    }

    public function test_the_catalogue_shows_only_the_offices_own_services(): void
    {
        $catalog = app(PricingRepository::class)->catalog(true, [$this->meterService]);

        $this->assertCount(1, $catalog);
        $this->assertSame($this->meterService, $catalog[0]['id']);
        $this->assertSame([$this->meterSub], array_column($catalog[0]['subServices'], 'id'));
        $this->assertFalse($catalog[0]['isTravel']);
    }

    public function test_an_office_can_run_several_services_at_once(): void
    {
        // The same company runs the city meter service AND sells corridors.
        $office = $this->office([$this->meterService, $this->travelService]);

        $this->assertSame([$this->meterService, $this->travelService], $office->serviceIds());

        $catalog = app(PricingRepository::class)->catalog(true, $office->serviceIds());

        $this->assertSame([$this->meterService, $this->travelService], array_column($catalog, 'id'));
        $this->assertSame(
            [$this->meterSub, $this->travelSub],
            array_merge(...array_map(fn ($s) => array_column($s['subServices'], 'id'), $catalog)),
        );
    }

    public function test_dropping_one_service_leaves_the_other_assigned(): void
    {
        $office = $this->office([$this->meterService, $this->travelService]);

        $office->services()->sync([$this->travelService]);

        $this->assertSame([$this->travelService], $office->fresh()->serviceIds());
    }

    public function test_a_travel_service_is_flagged_so_the_screen_can_send_it_to_corridors(): void
    {
        $catalog = app(PricingRepository::class)->catalog(true, [$this->travelService]);

        $this->assertTrue($catalog[0]['isTravel'], 'a travel service is priced per corridor, not by the metre');
    }

    public function test_the_whole_catalogue_is_still_available_when_no_service_is_given(): void
    {
        $this->assertCount(2, app(PricingRepository::class)->catalog(true));
    }

    public function test_an_unassigned_office_may_price_nothing_at_all(): void
    {
        // An empty list is NOT "no filter": showing the whole country catalogue
        // to an office assigned nothing would undo the boundary at exactly the
        // moment it matters.
        $this->assertSame([], app(PricingRepository::class)->catalog(true, []));

        app(PricingRepository::class)->syncPrices(1, [$this->meterSub => ['enabled' => 1, 'openPrice' => 7]], []);

        $this->assertSame(0, OfficeSubServicePrice::query()->where('office_id', 1)->count());
    }

    public function test_saving_cannot_price_a_sub_service_outside_the_offices_services(): void
    {
        // The form never renders it, so this is a hand-crafted POST — exactly
        // what the server-side boundary exists for.
        app(PricingRepository::class)->syncPrices(1, [
            $this->meterSub => ['enabled' => 1, 'openPrice' => 7],
            $this->travelSub => ['enabled' => 1, 'openPrice' => 999],
        ], [$this->meterService]);

        $rows = OfficeSubServicePrice::query()->where('office_id', 1)->pluck('sub_service_id')->all();

        $this->assertSame([$this->meterSub], $rows, 'the foreign sub-service must not be priced');
    }
}
