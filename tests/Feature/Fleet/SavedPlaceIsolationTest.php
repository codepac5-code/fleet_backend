<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\GeoServices\ShardContext;
use App\Models\InfrastructureNode;
use App\Models\SavedPlace;

/**
 * Saved places live on the GLOBAL table for both riders and drivers. A rider is
 * a global account, so their places follow them between countries on purpose —
 * a DRIVER belongs to one country and driver ids repeat across databases, so
 * driver #7 in Syria must never see driver #7's places in Qatar.
 */
class SavedPlaceIsolationTest extends FleetTestCase
{
    protected array $globalMigrations = [
        '2026_07_11_000003_create_saved_places_table.php',
        '2026_07_16_000001_add_driver_id_to_saved_places.php',
        '2026_07_28_000010_add_country_to_saved_places.php',
    ];

    protected function tearDown(): void
    {
        ShardContext::clear();
        parent::tearDown();
    }

    private function inCountry(string $code): void
    {
        ShardContext::set(new InfrastructureNode(['country_code' => $code]));
    }

    private function place(array $attributes): SavedPlace
    {
        // `user_id` is NOT NULL, so driver rows carry 0 the way production does.
        return SavedPlace::query()->create(array_merge([
            'user_id' => 0, 'label' => 'Home', 'title' => 'Somewhere', 'lat' => 33.5, 'lng' => 36.3,
        ], $attributes));
    }

    public function test_a_driver_place_is_stamped_with_the_active_country(): void
    {
        $this->inCountry('SY');

        $this->assertSame('sy', $this->place(['driver_id' => 7])->country_code);
    }

    public function test_two_countries_drivers_with_the_same_id_stay_apart(): void
    {
        $this->inCountry('SY');
        $this->place(['driver_id' => 7, 'title' => 'Damascus depot']);

        $this->inCountry('QA');
        $this->place(['driver_id' => 7, 'title' => 'Doha depot']);

        $this->inCountry('SY');
        $syrian = SavedPlace::query()->forDriver(7)->get();

        $this->assertCount(1, $syrian);
        $this->assertSame('Damascus depot', $syrian->first()->title);

        $this->inCountry('QA');
        $this->assertSame('Doha depot', SavedPlace::query()->forDriver(7)->first()->title);
    }

    public function test_legacy_unstamped_rows_still_belong_to_their_driver(): void
    {
        // Rows created before the stamp existed have a null country; hiding them
        // would silently lose a driver's saved places.
        $this->place(['driver_id' => 7, 'title' => 'Old pin']);

        $this->inCountry('SY');

        $this->assertSame('Old pin', SavedPlace::query()->forDriver(7)->first()->title);
    }

    public function test_rider_places_are_not_country_scoped(): void
    {
        $this->inCountry('SY');
        $this->place(['user_id' => 20, 'title' => 'Rider home']);

        $this->inCountry('QA');

        // A rider is one global account — their home does not disappear when
        // they book a ride in another country.
        $this->assertCount(1, SavedPlace::query()->where('user_id', 20)->get());
    }
}
