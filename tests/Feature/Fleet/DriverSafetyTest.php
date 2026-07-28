<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Const\Event\EventType;
use App\Models\Driver;
use App\Models\DriverSafetyContact;
use App\Models\DriverSafetyEvent;
use App\Models\DriverStatusLink;
use App\Models\EventOutbox;
use App\Models\RideBooking;

/**
 * Driver safety surface — DriverSafetyController, mounted under `driver/` with
 * the `driver` guard (routes/driver.php):
 *
 *   GET    driver/safety/contacts           list  -> data.items[]
 *   POST   driver/safety/contacts           create (201)
 *   DELETE driver/safety/contacts/{id}      delete -> data.ok
 *   POST   driver/safety/sos                raise  (201) -> data.{id,status,sharedWith}
 *   POST   driver/safety/sos/{id}/end       close  -> data.{id,status}
 *   POST   driver/safety/status-links       mint   (201) -> data.{id,url,expiresAt}
 *   DELETE driver/safety/status-links/{id}  revoke -> data.ok
 *
 * Contract details this file pins, because the older version of this test
 * asserted a shape the controller no longer returns:
 *
 *  - POST sos responds with {id, status, sharedWith} ONLY. There is no `kind`
 *    and no `office_id` in the payload; `kind` is persisted but not echoed.
 *  - office_id on the persisted row is resolved from the BOOKING's office when a
 *    booking is named (so a loaned/office-less driver's SOS still reaches the
 *    ride's office), falling back to the driver's own office otherwise.
 *  - lat/lng are `nullable` in the controller, so an SOS with no coordinates is
 *    accepted (201). See test_sos_without_coordinates_is_accepted.
 *  - the endpoint fans the alert out as a domain event on the driver + office
 *    (+ booking) channels. See test_sos_emits_a_realtime_event.
 *
 * Every write is scoped by `driver_id`, and a miss raises DomainException::
 * notFound() -> 404 (never 403): the API will not confirm that another
 * driver's SOS / contact / status link exists.
 */
class DriverSafetyTest extends FleetTestCase
{
    protected array $tenantMigrations = [
        '2026_06_25_000007_create_event_outbox_table.php',
        '2026_07_11_000001_create_ride_bookings_table.php',
        '2026_07_13_000001_create_driver_safety_events_table.php',
        // driver_safety_contacts + driver_status_links
        '2026_07_17_000001_create_driver_safety_misc_tables.php',
    ];

    /**
     * The controller reads `$request->user()->officeId`, so the acting driver
     * carries the office — nothing is looked up from the booking.
     */
    private function asDriver(int $id = 9, ?int $officeId = 3): self
    {
        $this->app['auth']->forgetGuards();
        $d = new Driver();
        $d->id = $id;
        $d->officeId = $officeId;

        return $this->actingAs($d, 'driver');
    }

    private function booking(int $id = 500, int $office = 3): void
    {
        $b = new RideBooking();
        $b->id = $id;
        $b->forceFill([
            'user_id' => 7, 'office_id' => $office, 'service' => 'ride', 'service_class' => 'standard',
            'pricing_style' => 'meter', 'status' => 'on_trip',
            'pickup_lat' => 25.1, 'pickup_lng' => 51.2, 'dropoff_lat' => 25.2, 'dropoff_lng' => 51.3,
            'currency_code' => 'USD',
        ]);
        $b->save();
    }

    /** Raise an SOS as $driverId and return the new event id. */
    private function raiseSos(int $driverId = 9, ?int $bookingId = 500): int
    {
        return (int) $this->asDriver($driverId)
            ->postJson('driver/safety/sos', ['booking_id' => $bookingId, 'lat' => 25.1, 'lng' => 51.2, 'hold_ms' => 1500])
            ->assertStatus(201)
            ->json('data.id');
    }

    // ── SOS ─────────────────────────────────────────────────────────────────

    public function test_sos_records_event_for_the_drivers_office(): void
    {
        $this->booking(500, 3);

        $res = $this->asDriver(9, 3)
            ->postJson('driver/safety/sos', ['booking_id' => 500, 'lat' => 25.1, 'lng' => 51.2, 'hold_ms' => 1500])
            ->assertStatus(201)
            ->assertJsonPath('data.status', 'open')
            ->assertJsonPath('data.sharedWith', ['fleetos', 'office', 'contacts']);

        $event = DriverSafetyEvent::query()->find($res->json('data.id'));

        $this->assertNotNull($event);
        $this->assertSame('sos', $event->kind);
        $this->assertSame(9, (int) $event->driver_id);
        $this->assertSame(500, (int) $event->booking_id);
        $this->assertSame(3, (int) $event->office_id);
        $this->assertSame(1500, (int) $event->hold_ms);
    }

    /**
     * office_id is resolved from the BOOKING. An office-less driver raising an
     * SOS on an office-owned booking must still route to that booking's office,
     * or the office console never sees the emergency.
     *
     * (This used to copy the driver's own officeId and write null here.)
     */
    public function test_sos_office_comes_from_the_booking(): void
    {
        $this->booking(501, 5);

        $id = $this->asDriver(9, null)
            ->postJson('driver/safety/sos', ['booking_id' => 501, 'lat' => 25.1, 'lng' => 51.2])
            ->assertStatus(201)
            ->json('data.id');

        $this->assertSame(5, (int) DriverSafetyEvent::query()->find($id)->office_id);
    }

    /**
     * The SOS is actually broadcast in realtime. The response has always
     * advertised sharedWith:['fleetos','office','contacts']; this asserts the
     * fan-out really happens — a support.message_created event on the driver
     * and office channels — rather than the row being written silently.
     */
    public function test_sos_emits_a_realtime_event(): void
    {
        $this->booking(500, 3);

        $id = $this->asDriver(9, 3)
            ->postJson('driver/safety/sos', ['booking_id' => 500, 'lat' => 25.1, 'lng' => 51.2])
            ->assertStatus(201)
            ->json('data.id');

        $event = EventOutbox::query()
            ->where('type', EventType::SUPPORT_MESSAGE_CREATED)
            ->first();

        $this->assertNotNull($event, 'the SOS must publish a domain event');
        $this->assertContains('admin', $event->channels, 'the fleet ops room must hear every SOS');
        $this->assertContains('driver.9', $event->channels);
        $this->assertContains('office.3', $event->channels);
        $this->assertContains('booking.500', $event->channels);
        $this->assertSame('sos', $event->payload['kind']);
        $this->assertSame($id, $event->payload['event_id']);
    }

    /** With no booking, the alert still reaches the driver's own office. */
    public function test_sos_without_booking_routes_to_the_drivers_office(): void
    {
        $id = $this->asDriver(9, 3)
            ->postJson('driver/safety/sos', ['lat' => 25.1, 'lng' => 51.2])
            ->assertStatus(201)
            ->json('data.id');

        $this->assertSame(3, (int) DriverSafetyEvent::query()->find($id)->office_id);

        $event = EventOutbox::query()->where('type', EventType::SUPPORT_MESSAGE_CREATED)->first();
        $this->assertContains('office.3', $event->channels);
    }

    /**
     * Coordinates are `nullable` in DriverSafetyController::sos, so a panic
     * press with no GPS fix is recorded rather than rejected. Pinned as the
     * real contract — an SOS is never dropped for want of a location.
     */
    public function test_sos_without_coordinates_is_accepted(): void
    {
        $id = $this->asDriver()->postJson('driver/safety/sos', ['booking_id' => 500])
            ->assertStatus(201)
            ->json('data.id');

        $event = DriverSafetyEvent::query()->find($id);
        $this->assertNull($event->lat);
        $this->assertNull($event->lng);
    }

    /** An SOS with no booking (driver not on a trip) is still recorded. */
    public function test_sos_without_booking_is_accepted(): void
    {
        $id = $this->asDriver()->postJson('driver/safety/sos', ['lat' => 25.1, 'lng' => 51.2])
            ->assertStatus(201)
            ->json('data.id');

        $this->assertNull(DriverSafetyEvent::query()->find($id)->booking_id);
    }

    public function test_end_sos_closes_the_event(): void
    {
        $id = $this->raiseSos(9);

        $this->asDriver(9)->postJson("driver/safety/sos/{$id}/end")
            ->assertStatus(200)
            ->assertJsonPath('data.id', $id)
            ->assertJsonPath('data.status', 'closed');

        $this->assertSame('closed', DriverSafetyEvent::query()->find($id)->status);
    }

    /** Ownership scoping: driver B must not be able to close driver A's SOS. */
    public function test_driver_cannot_end_another_drivers_sos(): void
    {
        $id = $this->raiseSos(9);

        $this->asDriver(99)->postJson("driver/safety/sos/{$id}/end")
            ->assertStatus(404)
            ->assertJsonPath('error.code', 'not_found');

        // still open — the foreign call must not have mutated anything
        $this->assertSame('open', DriverSafetyEvent::query()->find($id)->status);
    }

    public function test_end_unknown_sos_is_404(): void
    {
        $this->asDriver()->postJson('driver/safety/sos/424242/end')->assertStatus(404);
    }

    /**
     * There is no dedicated `POST driver/safety/report` route — DriverSafety
     * Controller has no `report` method, so the old test targeted a dead URI.
     * The de-facto channel is the sos endpoint with an explicit `kind`, which
     * test_sos_endpoint_records_non_sos_kinds covers. Left here, unfulfilled,
     * so the gap stays visible rather than silently disappearing.
     */
    public function test_report_records_safety_issue(): void
    {
        $this->markTestIncomplete(
            'No live equivalent: driver/safety/report is not routed and '
            . 'DriverSafetyController has no report() method.'
        );
    }

    /**
     * DriverSafetyController::sos validates optional `kind` and `category`, and
     * driver_safety_events carries both columns — so the one endpoint doubles
     * as the non-emergency report channel.
     */
    public function test_sos_endpoint_records_non_sos_kinds(): void
    {
        $this->booking(502, 3);

        $id = $this->asDriver()->postJson('driver/safety/sos', [
            'booking_id' => 502, 'kind' => 'report', 'category' => 'rider_behavior', 'note' => 'issue',
        ])->assertStatus(201)->json('data.id');

        $event = DriverSafetyEvent::query()->find($id);
        $this->assertSame('report', $event->kind);
        $this->assertSame('rider_behavior', $event->category);
        $this->assertSame('issue', $event->note);
    }

    // ── emergency contacts ──────────────────────────────────────────────────

    public function test_store_and_list_contacts(): void
    {
        $this->asDriver(9)->postJson('driver/safety/contacts', [
            'name' => 'Sara', 'phone' => '+97455000111', 'relation' => 'sister', 'is_primary' => true,
        ])->assertStatus(201)
            ->assertJsonPath('data.name', 'Sara')
            ->assertJsonPath('data.is_primary', true)
            // auto_share defaults to true when omitted
            ->assertJsonPath('data.auto_share', true);

        $this->asDriver(9)->getJson('driver/safety/contacts')
            ->assertStatus(200)
            ->assertJsonPath('data.items.0.name', 'Sara');
    }

    /** Primary contacts sort first, then by id. */
    public function test_contacts_list_puts_primary_first(): void
    {
        $this->asDriver(9)->postJson('driver/safety/contacts', ['name' => 'Ali', 'phone' => '+9741']);
        $this->asDriver(9)->postJson('driver/safety/contacts', ['name' => 'Sara', 'phone' => '+9742', 'is_primary' => true]);

        $items = $this->asDriver(9)->getJson('driver/safety/contacts')->json('data.items');
        $this->assertSame('Sara', $items[0]['name']);
        $this->assertSame('Ali', $items[1]['name']);
    }

    public function test_contacts_list_is_scoped_to_the_driver(): void
    {
        $this->asDriver(9)->postJson('driver/safety/contacts', ['name' => 'Sara', 'phone' => '+9742']);

        $this->asDriver(99)->getJson('driver/safety/contacts')
            ->assertStatus(200)
            ->assertJsonPath('data.items', []);
    }

    public function test_contact_requires_name_and_phone(): void
    {
        $this->asDriver()->postJson('driver/safety/contacts', ['name' => 'Sara'])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed');
    }

    public function test_destroy_contact(): void
    {
        $id = $this->asDriver(9)->postJson('driver/safety/contacts', ['name' => 'Sara', 'phone' => '+9742'])
            ->json('data.id');

        $this->asDriver(9)->deleteJson("driver/safety/contacts/{$id}")
            ->assertStatus(200)
            ->assertJsonPath('data.ok', true);

        $this->assertSame(0, DriverSafetyContact::query()->count());
    }

    /** Ownership scoping: driver B must not be able to delete driver A's contact. */
    public function test_driver_cannot_delete_another_drivers_contact(): void
    {
        $id = $this->asDriver(9)->postJson('driver/safety/contacts', ['name' => 'Sara', 'phone' => '+9742'])
            ->json('data.id');

        $this->asDriver(99)->deleteJson("driver/safety/contacts/{$id}")
            ->assertStatus(404)
            ->assertJsonPath('error.code', 'not_found');

        $this->assertSame(1, DriverSafetyContact::query()->count());
    }

    // ── shareable status links ──────────────────────────────────────────────

    public function test_create_status_link(): void
    {
        $res = $this->asDriver(9)->postJson('driver/safety/status-links', ['booking_id' => 500])
            ->assertStatus(201)
            ->assertJsonPath('data.url', fn ($url) => is_string($url) && str_contains($url, '/s/'));

        $link = DriverStatusLink::query()->find($res->json('data.id'));
        $this->assertSame(9, (int) $link->driver_id);
        $this->assertNull($link->revoked_at);
        $this->assertNotNull($res->json('data.expiresAt'));
    }

    /** Revoking stamps revoked_at; the row is kept for audit, not deleted. */
    public function test_destroy_status_link_revokes_it(): void
    {
        $id = $this->asDriver(9)->postJson('driver/safety/status-links', [])->json('data.id');

        $this->asDriver(9)->deleteJson("driver/safety/status-links/{$id}")
            ->assertStatus(200)
            ->assertJsonPath('data.ok', true);

        $this->assertNotNull(DriverStatusLink::query()->find($id)->revoked_at);
    }

    /** Ownership scoping: driver B must not be able to revoke driver A's link. */
    public function test_driver_cannot_revoke_another_drivers_status_link(): void
    {
        $id = $this->asDriver(9)->postJson('driver/safety/status-links', [])->json('data.id');

        $this->asDriver(99)->deleteJson("driver/safety/status-links/{$id}")
            ->assertStatus(404)
            ->assertJsonPath('error.code', 'not_found');

        $this->assertNull(DriverStatusLink::query()->find($id)->revoked_at);
    }
}
