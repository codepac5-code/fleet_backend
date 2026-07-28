<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Places\GeocodingProvider;
use App\Models\SavedPlace;
use App\Models\User;

/**
 * Place lookup for the pickup/dropoff pickers. The old URIs
 * (`user/places/autocomplete`, `user/places/{ref}`, `user/places/reverse`) are
 * all gone; the live surface is:
 *
 *   GET  user/places/suggest?q=…   saved places + Google predictions, merged
 *   GET  user/places/details?place_id=…
 *   POST user/geocode/reverse      {lat,lng}  ->  data.address
 *
 * Contract shifts that the old assertions had backwards:
 *  - suggest returns `data.results`, not a bare `data` array, and each row
 *    carries a `source` discriminator ('recent' for the rider's own saved
 *    places, 'google' for live predictions).
 *  - saved rows come from the GLOBAL connection (SavedPlace), Google rows from
 *    the injected GeocodingProvider. Saved always leads.
 *  - details is a lookup, not a resource: an unknown id is `data: null` with
 *    200, NOT a 404. Nothing 404s on this endpoint.
 *  - reverse moved to POST under `geocode` and returns `data.address` — the
 *    provider's `title` is remapped by GeocodeController.
 *
 * The empty-place_id filter lives in PlacesService::autocomplete (not in the
 * provider), so a junk prediction is dropped before it reaches the response.
 */
class PlacesTest extends FleetTestCase
{
    protected array $globalMigrations = [
        '2026_07_11_000003_create_saved_places_table.php',
        '2026_07_15_000001_add_rider_api_missing_columns.php',
        // Sending X-Country wakes ResolveTenantShard, which looks the country up
        // in infrastructure_nodes on the global connection.
        '2026_05_29_220120_create_infrastructure_nodes_table.php',
    ];

    protected array $tenantMigrations = [
        '2026_07_11_000001_create_ride_bookings_table.php',
        '2026_07_11_000004_add_titles_to_ride_bookings_table.php',
        '2026_07_15_000001_add_rider_api_missing_columns.php',
    ];

    /** Google calls the fake made, so we can prove when it is NOT consulted. */
    public static array $autocompleteCalls = [];

    protected function setUp(): void
    {
        parent::setUp();

        self::$autocompleteCalls = [];

        $this->app->bind(GeocodingProvider::class, fn () => new class implements GeocodingProvider {
            public function autocomplete(string $query, ?float $lat, ?float $lng, ?string $session, ?string $country = null): array
            {
                PlacesTest::$autocompleteCalls[] = ['q' => $query, 'country' => $country];

                return [
                    ['place_id' => 'p_doh', 'primary' => 'Hamad International Airport', 'secondary' => 'Doha', 'kind' => 'airport'],
                    // no place_id -> unusable, must never surface
                    ['place_id' => '', 'primary' => 'junk', 'secondary' => '', 'kind' => null],
                ];
            }

            public function details(string $placeId): ?array
            {
                return $placeId === 'p_doh'
                    ? ['place_id' => 'p_doh', 'title' => 'Hamad International Airport', 'lat' => 25.273, 'lng' => 51.608, 'kind' => 'airport']
                    : null;
            }

            public function reverse(float $lat, float $lng): ?array
            {
                return ['title' => 'Al Sadd, Doha', 'lat' => $lat, 'lng' => $lng];
            }
        });
    }

    private function asUser(int $id = 7): self
    {
        $user = new User();
        $user->id = $id;

        return $this->actingAs($user, 'user');
    }

    private function savedPlace(int $userId, string $title, string $address): SavedPlace
    {
        return SavedPlace::query()->create([
            'user_id' => $userId, 'label' => 'other', 'title' => $title,
            'address' => $address, 'lat' => 25.10, 'lng' => 51.10,
        ]);
    }

    // ── suggest ─────────────────────────────────────────────────────────────

    /** Predictions with no place_id are unusable and are dropped by PlacesService. */
    public function test_suggest_filters_predictions_without_a_place_id(): void
    {
        $res = $this->asUser()->getJson('user/places/suggest?q=ham')->assertStatus(200);

        $google = collect($res->json('data.results'))->where('source', 'google')->values();

        $this->assertCount(1, $google);
        $this->assertSame('p_doh', $google[0]['place_id']);
        $this->assertSame('Hamad International Airport', $google[0]['title']);
        $this->assertSame('Doha', $google[0]['address']);
    }

    /**
     * The rider's own saved places lead, and carry coordinates directly —
     * Google rows do not, because the app resolves those on tap via details().
     */
    public function test_suggest_puts_saved_places_before_google_and_marks_the_source(): void
    {
        $this->savedPlace(7, 'Hamad Clinic', 'Al Sadd, Doha');

        $results = $this->asUser(7)->getJson('user/places/suggest?q=Hamad')
            ->assertStatus(200)
            ->json('data.results');

        $this->assertSame('recent', $results[0]['source']);
        $this->assertSame('Hamad Clinic', $results[0]['title']);
        $this->assertSame(25.10, $results[0]['lat']);

        $this->assertSame('google', $results[1]['source']);
        $this->assertArrayNotHasKey('lat', $results[1]);
    }

    /**
     * Was `test_autocomplete_requires_query`. An empty q is no longer a 422 —
     * it is the picker's resting state: the rider's saved places, and no
     * (billable) Google call at all. That last part is the real invariant.
     */
    public function test_suggest_with_empty_query_returns_saved_places_and_skips_google(): void
    {
        $this->savedPlace(7, 'Home', 'West Bay, Doha');

        $results = $this->asUser(7)->getJson('user/places/suggest?q=')
            ->assertStatus(200)
            ->json('data.results');

        $this->assertCount(1, $results);
        $this->assertSame('recent', $results[0]['source']);
        $this->assertSame([], self::$autocompleteCalls);
    }

    /** Saved places are per-rider; user B never sees user A's. */
    public function test_suggest_saved_places_are_scoped_to_the_caller(): void
    {
        $this->savedPlace(7, 'Home', 'West Bay, Doha');

        $results = $this->asUser(8)->getJson('user/places/suggest?q=')
            ->assertStatus(200)
            ->json('data.results');

        $this->assertSame([], $results);
    }

    /** X-Country is forwarded so predictions stay inside the rider's country. */
    public function test_suggest_forwards_the_country_header_to_the_provider(): void
    {
        $this->asUser()->withHeader('X-Country', 'QA')
            ->getJson('user/places/suggest?q=ham')
            ->assertStatus(200);

        $this->assertSame('QA', self::$autocompleteCalls[0]['country']);
    }

    // ── details ─────────────────────────────────────────────────────────────

    /**
     * A hit returns the resolved coordinates. A miss is `data: null` with 200 —
     * this endpoint is a lookup, not a resource, so it never 404s. (The old test
     * asserted 404; that behaviour does not exist on the live route.)
     */
    public function test_details_returns_coordinates_and_null_for_unknown_ids(): void
    {
        $this->asUser()->getJson('user/places/details?place_id=p_doh')
            ->assertStatus(200)
            ->assertJsonPath('data.lat', 25.273)
            ->assertJsonPath('data.lng', 51.608)
            ->assertJsonPath('data.title', 'Hamad International Airport');

        $this->asUser()->getJson('user/places/details?place_id=nope')
            ->assertStatus(200)
            ->assertJsonPath('data', null);
    }

    public function test_details_without_a_place_id_is_null(): void
    {
        $this->asUser()->getJson('user/places/details')
            ->assertStatus(200)
            ->assertJsonPath('data', null);
    }

    // ── reverse geocode ─────────────────────────────────────────────────────

    /** The provider's `title` is remapped to `address` by GeocodeController. */
    public function test_reverse_geocode_returns_the_formatted_address(): void
    {
        $this->asUser()->postJson('user/geocode/reverse', ['lat' => 25.28, 'lng' => 51.53])
            ->assertStatus(200)
            ->assertJsonPath('data.address', 'Al Sadd, Doha');
    }

    public function test_reverse_geocode_rejects_out_of_range_coordinates(): void
    {
        $this->asUser()->postJson('user/geocode/reverse', ['lat' => 91, 'lng' => 51.53])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed');

        $this->asUser()->postJson('user/geocode/reverse', ['lat' => 25.28, 'lng' => 181])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed');
    }

    public function test_reverse_geocode_requires_both_coordinates(): void
    {
        $this->asUser()->postJson('user/geocode/reverse', ['lat' => 25.28])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed');
    }

    /**
     * GAP: recent dropoffs have no live route.
     *
     * The old `GET user/places/recent` is gone, and nothing replaced it — but
     * the logic survives, fully implemented and unreachable, in
     * PlacesService::recent() (dedups by dropoff coordinate rounded to 4dp,
     * newest first, limit 10) on top of
     * RideBookingRepository::recentDropoffsForUser(), whose ONLY caller is that
     * dead method. Skipped rather than deleted so the orphaned code stays
     * visible; wiring a route back up is a separate change.
     */
    public function test_recent_dropoffs_endpoint_is_missing(): void
    {
        $this->markTestSkipped(
            'No live route exposes PlacesService::recent(); GET user/places/recent was removed. '
            . 'Dedup-by-dropoff logic is implemented but unreachable over HTTP.'
        );
    }
}
