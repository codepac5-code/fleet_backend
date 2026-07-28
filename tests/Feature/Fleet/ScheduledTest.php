<?php

namespace Tests\Feature\Fleet;

use App\Models\Office;
use App\Models\RideBooking;
use App\Models\ServiceTariff;
use App\Models\User;

/**
 * Scheduled (pre-booked) rides. The old `user/scheduled/bookings[/{id}][/cancel]`
 * family is gone; the live surface is a plain resource:
 *
 *   POST   user/scheduled        create   (201)
 *   GET    user/scheduled/{id}   show
 *   PATCH  user/scheduled/{id}   amend
 *   DELETE user/scheduled/{id}   cancel   (204, no body)
 *
 * Contract shifts the old assertions had wrong:
 *  - the request is NESTED and camelCase: the route lives under `route`, and
 *    the time/flight fields are `scheduledFor` / `flightNo` (not
 *    `scheduled_at` / `flight_no`). The RESPONSE is still snake_case, because
 *    it is BookingPresenter::row() — so `data.id`, not `data.booking_id`.
 *  - `data.timeline` was replaced by `data.steps`, a keyed list
 *    (scheduled / matching / assigned / completed) with a `status` of
 *    done|current|pending. ScheduledRideService::show() re-presents through
 *    present(), so the ScheduledService `timeline` shape never reaches HTTP.
 *  - cancel is DELETE and returns 204 with an EMPTY body, so the old
 *    `data.status === cancelled` assertion has nothing to read; the state is
 *    verified against the row instead.
 *  - there is NO list endpoint any more (see the gap test at the bottom).
 */
class ScheduledTest extends FleetTestCase
{
    protected array $globalMigrations = [
        '2026_07_15_000001_add_rider_api_missing_columns.php',
    ];

    protected array $tenantMigrations = [
        '2024_10_26_104402_create_services_table.php',
        '2024_10_26_104427_create_sub_services_table.php',
        '2024_10_29_211028_create_offices_table.php',
        '2026_01_03_025343_create_office_sub_service_prices_table.php',
        '2026_06_25_000005_create_dispatch_jobs_table.php',
        '2026_06_25_000017_create_ride_ratings_table.php',
        '2026_07_01_000002_create_service_tariffs_table.php',
        '2026_07_11_000001_create_ride_bookings_table.php',
        '2026_07_11_000004_add_titles_to_ride_bookings_table.php',
        '2026_07_11_000008_add_schedule_to_ride_bookings_table.php',
        '2026_07_11_000009_add_change_revision_to_ride_bookings_table.php',
        '2026_07_14_000001_add_office_booking_fields_to_ride_bookings.php',
        '2026_07_15_000001_add_rider_api_missing_columns.php',
    ];

    private const CITY = [25.2854, 51.5310];
    private const AIRPORT = [25.2731, 51.6080];

    private function asUser(int $id = 7): self
    {
        $u = new User();
        $u->id = $id;

        return $this->actingAs($u, 'user');
    }

    private function office(): Office
    {
        return Office::query()->create([
            'officeName' => 'Al Fleet', 'email' => 'o@x.qa', 'password' => 'x',
            'contactNumber' => '33001234', 'address' => 'West Bay, Doha', 'country' => 'QA',
            'city' => 'Doha', 'region' => 'Doha', 'status' => 1, 'is_verified' => true,
            'lat' => self::CITY[0], 'lng' => self::CITY[1],
        ]);
    }

    private function tariff(int $officeId, int $fixed = 5000): void
    {
        ServiceTariff::query()->create([
            'office_id' => $officeId, 'service' => 'travel', 'service_class' => 'standard',
            'currency_code' => 'USD', 'pricing_style' => 'fixed', 'fixed_minor' => $fixed,
        ]);
    }

    private function createPayload(int $officeId, array $override = []): array
    {
        return array_merge([
            'office_id' => $officeId,
            'route' => [
                'pickup' => ['lat' => self::CITY[0], 'lng' => self::CITY[1], 'title' => 'Al Sadd'],
                'dropoff' => ['lat' => self::AIRPORT[0], 'lng' => self::AIRPORT[1], 'title' => 'Airport'],
                'service' => 'travel', 'serviceClass' => 'standard',
            ],
            'scheduledFor' => '2026-08-16T04:30:00Z',
            'passengers' => 2, 'luggage' => 3, 'flightNo' => 'QR8412',
        ], $override);
    }

    /** Create and return the new booking id. */
    private function schedule(int $officeId, int $userId = 7, array $override = []): int
    {
        return $this->asUser($userId)
            ->postJson('user/scheduled', $this->createPayload($officeId, $override))
            ->assertStatus(201)
            ->json('data.id');
    }

    /** A travel sub-service with meter rates (open + per-km + per-min). */
    private function subService(int $id = 1, float $open = 3, float $km = 2, float $minute = 0.5): void
    {
        \App\Models\Service::query()->firstOrCreate(['id' => 1], ['image' => 'x', 'title' => 'Travel', 'title_en' => 'Travel', 'status' => 1]);
        \App\Models\SubService::query()->create([
            'id' => $id, 'name' => 'Comfort', 'name_en' => 'Comfort', 'serviceId' => 1,
            'openPrice' => $open, 'kmPrice' => $km, 'minutePrice' => $minute, 'is_travel' => true, 'status' => 1,
        ]);
    }

    // ── meter (direct-to-driver) scheduled ────────────────────────────────────

    public function test_meter_scheduled_prices_from_the_sub_service(): void
    {
        app()->instance('shard_currency', 'USD');
        $office = $this->office();
        $this->subService(1); // no service_tariff needed — meter is priced from the sub-service

        $res = $this->asUser()->postJson('user/scheduled', $this->createPayload($office->id, [
            'sub_service_id' => 1,
        ]))->assertStatus(201);

        $res->assertJsonPath('data.trip_type', 'meter')
            ->assertJsonPath('data.pricing_style', 'meter');
        // Priced by the meter (open + km + min), so a real positive fare.
        $this->assertGreaterThan(0, (int) $res->json('data.fare_minor'));
        // Meter trips skip the office-acceptance step in the shared timeline.
        $keys = array_column($res->json('data.steps'), 'key');
        $this->assertNotContains('office_confirmed', $keys);
    }

    // ── create ──────────────────────────────────────────────────────────────

    public function test_create_scheduled_booking(): void
    {
        $office = $this->office();
        $this->tariff($office->id, 5000);

        $res = $this->asUser()->postJson('user/scheduled', $this->createPayload($office->id))
            ->assertStatus(201)
            ->assertJsonPath('data.status', 'scheduled')
            ->assertJsonPath('data.service', 'travel')
            ->assertJsonPath('data.service_class', 'standard')
            // priced through the tariff engine, not echoed from the request
            ->assertJsonPath('data.pricing_style', 'fixed')
            ->assertJsonPath('data.fare_minor', 5000)
            ->assertJsonPath('data.total_minor', 5000)
            ->assertJsonPath('data.currency_code', 'USD')
            // camelCase in, snake_case out
            ->assertJsonPath('data.flight_no', 'QR8412')
            ->assertJsonPath('data.passengers', 2)
            ->assertJsonPath('data.luggage', 3)
            ->assertJsonPath('data.pickup_title', 'Al Sadd')
            ->assertJsonPath('data.dropoff_title', 'Airport')
            ->assertJsonPath('data.office.officeName', 'Al Fleet');

        $this->assertNotNull($res->json('data.scheduled_at'));
        $this->assertSame(7, $res->json('data.user_id'));
    }

    /** A freshly scheduled ride is booked but unmatched: step 1 is `current`. */
    public function test_create_returns_the_progress_steps(): void
    {
        $office = $this->office();
        $this->tariff($office->id);

        $steps = $this->asUser()->postJson('user/scheduled', $this->createPayload($office->id))
            ->assertStatus(201)
            ->json('data.steps');

        $this->assertSame(['scheduled', 'matching', 'assigned', 'completed'], array_column($steps, 'key'));
        $this->assertSame('done', $steps[0]['status']);
        $this->assertSame('pending', $steps[1]['status']);
        $this->assertSame('pending', $steps[2]['status']);
        $this->assertNotNull($steps[0]['at']);
    }

    public function test_create_missing_time_is_422(): void
    {
        $office = $this->office();
        $this->tariff($office->id);

        $payload = $this->createPayload($office->id);
        unset($payload['scheduledFor']);

        $this->asUser()->postJson('user/scheduled', $payload)
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed');
    }

    public function test_create_rejects_bad_coordinates(): void
    {
        $office = $this->office();
        $this->tariff($office->id);

        $payload = $this->createPayload($office->id);
        $payload['route']['pickup']['lat'] = 91;

        $this->asUser()->postJson('user/scheduled', $payload)
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed');
    }

    public function test_create_requires_a_service_class(): void
    {
        $office = $this->office();
        $this->tariff($office->id);

        $payload = $this->createPayload($office->id);
        unset($payload['route']['serviceClass']);

        $this->asUser()->postJson('user/scheduled', $payload)
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed');
    }

    /**
     * An office with no tariff for this service+class cannot quote, so the
     * booking is refused outright rather than created unpriced.
     */
    public function test_create_without_a_matching_tariff_is_404(): void
    {
        $this->asUser()->postJson('user/scheduled', $this->createPayload(999))
            ->assertStatus(404)
            ->assertJsonPath('error.code', 'tariff_not_found');

        $this->assertSame(0, RideBooking::query()->count());
    }

    // ── show / update / cancel ──────────────────────────────────────────────

    public function test_show_returns_the_booking_with_steps(): void
    {
        $office = $this->office();
        $this->tariff($office->id);
        $id = $this->schedule($office->id);

        $this->asUser()->getJson("user/scheduled/{$id}")
            ->assertStatus(200)
            ->assertJsonPath('data.id', $id)
            ->assertJsonPath('data.status', 'scheduled')
            ->assertJsonPath('data.flight_no', 'QR8412')
            ->assertJsonPath('data.steps.1.key', 'matching')
            ->assertJsonPath('data.steps.2.key', 'assigned')
            ->assertJsonPath('data.office.officeName', 'Al Fleet');
    }

    public function test_show_unknown_booking_is_404(): void
    {
        $this->asUser()->getJson('user/scheduled/424242')->assertStatus(404);
    }

    /** Amending returns the updated row and bumps the change counter. */
    public function test_update_amends_and_bumps_change_revision(): void
    {
        $office = $this->office();
        $this->tariff($office->id);
        $id = $this->schedule($office->id);

        $this->asUser()->patchJson("user/scheduled/{$id}", [
            'passengers' => 4, 'luggage' => 0, 'flightNo' => 'QR9001',
        ])
            ->assertStatus(200)
            ->assertJsonPath('data.passengers', 4)
            ->assertJsonPath('data.luggage', 0)
            ->assertJsonPath('data.flight_no', 'QR9001')
            ->assertJsonPath('data.change_revision', 1);
    }

    public function test_update_rejects_zero_passengers(): void
    {
        $office = $this->office();
        $this->tariff($office->id);
        $id = $this->schedule($office->id);

        $this->asUser()->patchJson("user/scheduled/{$id}", ['passengers' => 0])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed');
    }

    /** Cancel is DELETE and returns 204 with no body — verify against the row. */
    public function test_cancel_scheduled_booking(): void
    {
        $office = $this->office();
        $this->tariff($office->id);
        $id = $this->schedule($office->id);

        $this->asUser()->deleteJson("user/scheduled/{$id}")
            ->assertStatus(204)
            ->assertNoContent();

        $booking = RideBooking::query()->find($id);
        $this->assertSame('cancelled', $booking->status);
        $this->assertNotNull($booking->cancelled_at);
    }

    /** A cancelled ride is terminal: it can be neither re-cancelled nor amended. */
    public function test_cancelled_booking_is_not_cancellable_or_editable(): void
    {
        $office = $this->office();
        $this->tariff($office->id);
        $id = $this->schedule($office->id);

        $this->asUser()->deleteJson("user/scheduled/{$id}")->assertStatus(204);

        $this->asUser()->deleteJson("user/scheduled/{$id}")
            ->assertStatus(409)
            ->assertJsonPath('error.code', 'not_cancellable');

        $this->asUser()->patchJson("user/scheduled/{$id}", ['passengers' => 4])
            ->assertStatus(409)
            ->assertJsonPath('error.code', 'not_editable');
    }

    // ── ownership (the security boundary) ───────────────────────────────────

    /** A stranger gets 404, not 403 — the API will not confirm the ride exists. */
    public function test_foreign_rider_cannot_read_a_scheduled_booking(): void
    {
        $office = $this->office();
        $this->tariff($office->id);
        $id = $this->schedule($office->id, 7);

        $this->asUser(8)->getJson("user/scheduled/{$id}")->assertStatus(404);
    }

    public function test_foreign_rider_cannot_amend_or_cancel(): void
    {
        $office = $this->office();
        $this->tariff($office->id);
        $id = $this->schedule($office->id, 7);

        $this->asUser(8)->patchJson("user/scheduled/{$id}", ['passengers' => 9])->assertStatus(404);
        $this->asUser(8)->deleteJson("user/scheduled/{$id}")->assertStatus(404);

        // …and the ride is untouched.
        $booking = RideBooking::query()->find($id);
        $this->assertSame('scheduled', $booking->status);
        $this->assertSame(2, (int) $booking->passengers);
    }

    // ── gaps ────────────────────────────────────────────────────────────────

    /**
     * GAP: no way to LIST your scheduled rides.
     *
     * `GET user/scheduled/bookings` is gone and only `POST user/scheduled` is
     * registered on the collection URI, so `GET user/scheduled` 405s. The logic
     * exists but is unreachable: ScheduledService::list() and
     * RideBookingRepository::scheduledForUser() have no HTTP caller. Skipped
     * rather than deleted so the orphan stays visible.
     */
    public function test_scheduled_list_endpoint_is_missing(): void
    {
        $this->markTestSkipped(
            'No live route lists a rider\'s scheduled bookings. ScheduledService::list() '
            . 'and RideBookingRepository::scheduledForUser() are implemented but unrouted.'
        );
    }

    /**
     * GAP: the pre-booking office comparison is unrouted.
     *
     * `POST user/scheduled/offers` is gone. ScheduledService::offers() still
     * builds the cheapest-first list with per-service perks (`flight_tracking`,
     * `meet_greet`) and free-wait windows (60 min travel / 30 min ride), but
     * nothing calls it. `POST user/offices/search` is the nearest live
     * endpoint and is covered in MarketplaceTest — it ranks by DISTANCE and
     * carries no perks or free-wait, so it is not a substitute.
     */
    public function test_scheduled_offers_endpoint_is_missing(): void
    {
        $this->markTestSkipped(
            'No live route exposes ScheduledService::offers(); POST user/scheduled/offers was removed. '
            . 'Cheapest-first ranking, perks and free_wait_min are implemented but unreachable over HTTP.'
        );
    }
}
