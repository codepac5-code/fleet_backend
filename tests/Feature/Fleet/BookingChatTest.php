<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Const\Dispatch\DispatchStatus;
use App\Http\Core\Const\Event\EventType;
use App\Models\CommissionSnapshot;
use App\Models\DispatchJob;
use App\Models\Driver;
use App\Models\EventOutbox;
use App\Models\RideBooking;
use App\Models\User;

/**
 * Shared rider<->driver trip chat, served by the multi-guard routes registered
 * inline in bootstrap/app.php:
 *
 *   GET  bookings/{id}/chat        history  (paginated: data.items + nextCursor)
 *   POST bookings/{id}/chat        send     (201)
 *   POST bookings/{id}/chat/read   mark read
 *
 * These carry `auth:user,driver`, so BOTH apps authenticate against the same
 * URI and the principal is inferred from whichever guard resolves
 * (BookingChatController::principal). That makes participant scoping the
 * security boundary here, which is what most of this file exercises.
 *
 * Two contract details worth pinning, because they are easy to get wrong:
 *  - a non-participant gets 404 (DomainException::notFound), NOT 403 — the
 *    endpoint refuses to confirm the booking exists to a stranger.
 *  - driver participation is keyed off ride_bookings.driver_id, NOT the
 *    DispatchJob assignment; the dispatch row only governs chat_unavailable.
 */
class BookingChatTest extends FleetTestCase
{
    protected array $tenantMigrations = [
        '2026_06_24_000004_create_commission_snapshots_table.php',
        '2026_06_25_000005_create_dispatch_jobs_table.php',
        '2026_06_25_000007_create_event_outbox_table.php',
        '2026_07_11_000001_create_ride_bookings_table.php',
        '2026_07_11_000004_add_titles_to_ride_bookings_table.php',
        '2026_07_11_000005_create_booking_chat_messages_table.php',
        // supplies ride_bookings.driver_id, which the participant check reads
        '2026_07_15_000001_add_rider_api_missing_columns.php',
    ];

    private function rider(int $id = 7): self
    {
        $this->app['auth']->forgetGuards();
        $u = new User();
        $u->id = $id;

        return $this->actingAs($u, 'user');
    }

    private function driver(int $id = 9): self
    {
        $this->app['auth']->forgetGuards();
        $d = new Driver();
        $d->id = $id;

        return $this->actingAs($d, 'driver');
    }

    /** Booking owned by $userId and (optionally) driven by $driverId. */
    private function booking(int $userId = 7, ?int $driverId = 9): RideBooking
    {
        return RideBooking::query()->create([
            'user_id' => $userId, 'office_id' => 3, 'service' => 'travel', 'service_class' => 'standard',
            'pricing_style' => 'fixed', 'status' => 'assigned', 'driver_id' => $driverId,
            'pickup_lat' => 25.28, 'pickup_lng' => 51.53, 'dropoff_lat' => 25.27, 'dropoff_lng' => 51.60,
            'currency_code' => 'USD', 'fare_minor' => 5000, 'total_minor' => 5000,
        ]);
    }

    private function assign(int $bookingId, int $driverId = 9, string $status = DispatchStatus::ASSIGNED): void
    {
        DispatchJob::query()->create([
            'booking_id' => $bookingId, 'office_id' => 3, 'service_class' => 'standard',
            'lat' => 25.28, 'lng' => 51.53, 'status' => $status,
            'assigned_driver_id' => $status === DispatchStatus::ASSIGNED ? $driverId : null, 'wave' => 1,
        ]);
    }

    private function settle(int $bookingId, int $driverId = 9): void
    {
        CommissionSnapshot::query()->create([
            'booking_id' => $bookingId, 'office_id' => 3, 'driver_id' => $driverId, 'currency_code' => 'USD',
            'pricing_style' => 'fixed', 'fare_minor' => 5000, 'discount_minor' => 0, 'total_minor' => 5000,
            'fleet_rate' => 10, 'office_rate' => 10, 'fleet_minor' => 500, 'office_minor' => 500, 'driver_minor' => 4000,
        ]);
    }

    // ── chat window ─────────────────────────────────────────────────────────

    public function test_chat_unavailable_before_assignment(): void
    {
        $b = $this->booking();
        $this->assign($b->id, 9, DispatchStatus::PENDING);

        $this->rider()->postJson("bookings/{$b->id}/chat", ['body' => 'hi'])
            ->assertStatus(403)
            ->assertJsonPath('error.code', 'chat_unavailable');
    }

    public function test_chat_closed_after_settlement(): void
    {
        $b = $this->booking(7);
        $this->assign($b->id, 9);
        $this->settle($b->id);

        $this->rider(7)->postJson("bookings/{$b->id}/chat", ['body' => 'hello'])
            ->assertStatus(403)
            ->assertJsonPath('error.code', 'chat_closed');
    }

    /** Reading history stays allowed after settlement — only sending closes. */
    public function test_history_still_readable_after_settlement(): void
    {
        $b = $this->booking(7);
        $this->assign($b->id, 9);
        $this->rider(7)->postJson("bookings/{$b->id}/chat", ['body' => 'before settle'])->assertStatus(201);
        $this->settle($b->id);

        $res = $this->rider(7)->getJson("bookings/{$b->id}/chat")->assertStatus(200);
        $this->assertCount(1, $res->json('data.items'));
    }

    // ── participant scoping (the security boundary) ─────────────────────────

    public function test_foreign_rider_cannot_read_chat(): void
    {
        $b = $this->booking(7);
        $this->assign($b->id, 9);

        $this->rider(8)->getJson("bookings/{$b->id}/chat")->assertStatus(404);
    }

    public function test_foreign_rider_cannot_send_chat(): void
    {
        $b = $this->booking(7);
        $this->assign($b->id, 9);

        $this->rider(8)->postJson("bookings/{$b->id}/chat", ['body' => 'let me in'])
            ->assertStatus(404);
    }

    /** A driver token only reaches trips that driver is actually assigned to. */
    public function test_foreign_driver_cannot_read_chat(): void
    {
        $b = $this->booking(7, 9);
        $this->assign($b->id, 9);

        $this->driver(99)->getJson("bookings/{$b->id}/chat")->assertStatus(404);
    }

    public function test_foreign_driver_cannot_send_chat(): void
    {
        $b = $this->booking(7, 9);
        $this->assign($b->id, 9);

        $this->driver(99)->postJson("bookings/{$b->id}/chat", ['body' => 'wrong driver'])
            ->assertStatus(404);
    }

    public function test_foreign_party_cannot_mark_read(): void
    {
        $b = $this->booking(7, 9);
        $this->assign($b->id, 9);

        $this->rider(8)->postJson("bookings/{$b->id}/chat/read")->assertStatus(404);
        $this->driver(99)->postJson("bookings/{$b->id}/chat/read")->assertStatus(404);
    }

    /**
     * Guard-collision guard: a DRIVER whose id equals the booking's user_id must
     * not inherit the rider's seat. principal() resolves the driver guard first,
     * so this pins that the id is checked against driver_id, not user_id.
     */
    public function test_driver_with_same_id_as_rider_is_not_a_participant(): void
    {
        $b = $this->booking(7, 9);   // rider 7, driver 9
        $this->assign($b->id, 9);

        $this->driver(7)->getJson("bookings/{$b->id}/chat")->assertStatus(404);
    }

    public function test_unknown_booking_is_404(): void
    {
        $this->rider(7)->getJson('bookings/424242/chat')->assertStatus(404);
    }

    // ── exchange ────────────────────────────────────────────────────────────

    public function test_rider_and_driver_exchange_messages(): void
    {
        $b = $this->booking(7, 9);
        $this->assign($b->id, 9);

        $this->rider(7)->postJson("bookings/{$b->id}/chat", ['body' => 'Coming out now'])
            ->assertStatus(201)
            ->assertJsonPath('data.from_type', 'rider');

        $this->driver(9)->postJson("bookings/{$b->id}/chat", ['body' => 'At Gate 2'])
            ->assertStatus(201)
            ->assertJsonPath('data.from_type', 'driver');

        $res = $this->rider(7)->getJson("bookings/{$b->id}/chat")->assertStatus(200);
        $items = $res->json('data.items');
        $this->assertCount(2, $items);
        // history is returned oldest-first
        $this->assertSame('Coming out now', $items[0]['body']);
        $this->assertSame('rider', $items[0]['from_type']);
        $this->assertSame('driver', $items[1]['from_type']);
    }

    public function test_send_emits_chat_event_on_the_booking_channel(): void
    {
        $b = $this->booking(7, 9);
        $this->assign($b->id, 9);

        $this->rider(7)->postJson("bookings/{$b->id}/chat", ['body' => 'ping'])->assertStatus(201);

        $event = EventOutbox::query()->where('type', EventType::BOOKING_CHAT_MESSAGE)->first();
        $this->assertNotNull($event);
        $this->assertContains('booking.' . $b->id, $event->channels);
    }

    public function test_read_marks_other_party_messages(): void
    {
        $b = $this->booking(7, 9);
        $this->assign($b->id, 9);

        $this->driver(9)->postJson("bookings/{$b->id}/chat", ['body' => 'Where are you?'])->assertStatus(201);
        $this->rider(7)->postJson("bookings/{$b->id}/chat/read")->assertStatus(200);

        $res = $this->rider(7)->getJson("bookings/{$b->id}/chat");
        $this->assertNotNull($res->json('data.items.0.read_at'));
    }

    // ── validation ──────────────────────────────────────────────────────────

    public function test_missing_body_is_422(): void
    {
        $b = $this->booking(7, 9);
        $this->assign($b->id, 9);

        $this->rider(7)->postJson("bookings/{$b->id}/chat", [])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed');
    }

    /**
     * Whitespace-only bodies are rejected at the validation layer, not by the
     * service: Laravel's `required` rule trims strings before testing them, so
     * '   ' fails `required` and never reaches BookingChatService::send.
     *
     * That makes the service's own `empty_message` guard unreachable via HTTP.
     * It is still worth keeping as a defence for non-HTTP callers, but nothing
     * should depend on that error code coming back from this endpoint.
     */
    public function test_whitespace_only_body_is_rejected_by_validation(): void
    {
        $b = $this->booking(7, 9);
        $this->assign($b->id, 9);

        $this->rider(7)->postJson("bookings/{$b->id}/chat", ['body' => '   '])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed');
    }

    public function test_overlong_body_is_422(): void
    {
        $b = $this->booking(7, 9);
        $this->assign($b->id, 9);

        $this->rider(7)->postJson("bookings/{$b->id}/chat", ['body' => str_repeat('a', 2001)])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed');
    }
}
