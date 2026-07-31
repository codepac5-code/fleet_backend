<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Ride\RideBookingService;
use App\Http\Core\Classes\Support\RiderSupportService;
use App\Http\Core\Const\Event\EventType;
use App\Http\Core\Exceptions\DomainException;
use App\Models\Complaint;
use App\Models\EventOutbox;
use App\Models\RideBooking;
use App\Models\User;

/**
 * Rider support + safety.
 *
 * The HTTP surface moved off the old `api/v1/support/*` and `api/v1/safety/*`
 * prefixes onto the `user/` group (routes/user.php), and the shape changed with
 * it — the controllers now sit behind App\Http\Services\User\Support\Logic\
 * SupportService, which wraps the older Core RiderSupportService:
 *
 *   POST user/tickets      {topic, tripId?, message}  -> 201 data.{ticketId,status}
 *   GET  user/tickets                                 -> data[] (rows WITH messages)
 *   GET  user/tickets/{id}                            -> data.{...,messages[]}
 *   POST user/complaints   {about, tripId?, description, photoUrl?}
 *                                                     -> 201 data.{id,routed_to,priority,case_ref,status}
 *   GET  t/{slug}                                     -> public shared-trip page
 *
 * Two things to know before reading the assertions:
 *
 *  1. `topic` is used as BOTH the category and the subject (SupportService::
 *     openTicket calls open($uid, $topic, $tripId, $topic, $message)). The
 *     office/fleetos `layer` is therefore derived from the topic string via
 *     SupportLayer::forCategory, and only office-layer tickets get an office_id.
 *  2. POST user/tickets returns ONLY {ticketId, status} — layer/office_id are
 *     not echoed on create, so those are asserted by reading the ticket back.
 *
 * Several capabilities still exist on RiderSupportService but have NO route:
 * rider reply, safety report, rider SOS, office call-info, and trip share
 * (RideBookingService::share). Those are exercised at the service level below
 * and are called out in the section headers — see the report accompanying this
 * file for the list of unrouted endpoints.
 */
class SupportSafetyTest extends FleetTestCase
{
    /** complaints lives on the `global` connection (Complaint::$connection). */
    protected array $globalMigrations = [
        '2026_07_15_000001_add_rider_api_missing_columns.php',
        '2026_07_25_000005_add_country_to_support_tables.php',
        '2026_07_28_000001_add_office_to_complaints.php',
    ];

    protected array $tenantMigrations = [
        // the public shared-trip page resolves the live status from the dispatch
        // job plus the settlement snapshot (RideBookingService::effectiveStatus)
        '2026_06_24_000004_create_commission_snapshots_table.php',
        '2026_06_25_000005_create_dispatch_jobs_table.php',
        // ...and names the office, whose summary carries its rating average
        '2026_06_25_000017_create_ride_ratings_table.php',
        '2026_06_25_000007_create_event_outbox_table.php',
        '2026_07_11_000001_create_ride_bookings_table.php',
        '2026_07_11_000004_add_titles_to_ride_bookings_table.php',
        '2026_07_11_000006_create_rider_support_tables.php',
        // supplies rider_support_tickets.topic, read by SupportService::ticketRow
        '2026_07_15_000001_add_rider_api_missing_columns.php',
        '2026_07_25_000005_add_country_to_support_tables.php',
        '2026_07_28_000001_add_office_to_complaints.php',
    ];

    private function asUser(int $id = 7): self
    {
        $this->app['auth']->forgetGuards();
        $u = new User();
        $u->id = $id;

        return $this->actingAs($u, 'user');
    }

    private function booking(int $userId = 7, int $office = 3): RideBooking
    {
        return RideBooking::query()->create([
            'user_id' => $userId, 'office_id' => $office, 'service' => 'travel', 'service_class' => 'standard',
            'pricing_style' => 'fixed', 'status' => 'assigned',
            'pickup_lat' => 25.28, 'pickup_lng' => 51.53, 'dropoff_lat' => 25.27, 'dropoff_lng' => 51.60,
            'currency_code' => 'USD', 'fare_minor' => 5000, 'total_minor' => 5000,
        ]);
    }

    private function support(): RiderSupportService
    {
        return app(RiderSupportService::class);
    }

    private function rides(): RideBookingService
    {
        return app(RideBookingService::class);
    }

    /** Open a ticket over HTTP and return its id. */
    private function openTicket(int $userId, string $topic, ?int $tripId, string $message = 'body'): int
    {
        return (int) $this->asUser($userId)
            ->postJson('user/tickets', array_filter([
                'topic' => $topic, 'tripId' => $tripId, 'message' => $message,
            ], fn ($v) => $v !== null))
            ->assertStatus(201)
            ->json('data.ticketId');
    }

    // ── ticket routing: office vs fleetos layer ─────────────────────────────

    /**
     * An office-layer topic on an owned booking resolves the booking's office.
     * Create only returns {ticketId, status}, so the routing is read back.
     */
    public function test_office_layer_ticket_resolves_office(): void
    {
        $b = $this->booking(7, 3);

        $id = $this->openTicket(7, 'lost_item', $b->id, 'Black backpack');

        $this->asUser(7)->getJson("user/tickets/{$id}")
            ->assertStatus(200)
            ->assertJsonPath('data.layer', 'office')
            ->assertJsonPath('data.office_id', 3)
            ->assertJsonPath('data.category', 'lost_item');
    }

    public function test_fleetos_layer_ticket_has_no_office(): void
    {
        $id = $this->openTicket(7, 'refund', null, 'Overcharged');

        $this->asUser(7)->getJson("user/tickets/{$id}")
            ->assertStatus(200)
            ->assertJsonPath('data.layer', 'fleetos')
            ->assertJsonPath('data.office_id', null);
    }

    /**
     * An unknown topic falls through SupportLayer::forCategory's `??` to
     * fleetos — it is never silently attributed to an office.
     */
    public function test_unknown_topic_defaults_to_fleetos(): void
    {
        $id = $this->openTicket(7, 'other', null, 'Something else');

        $this->asUser(7)->getJson("user/tickets/{$id}")
            ->assertJsonPath('data.layer', 'fleetos')
            ->assertJsonPath('data.office_id', null);
    }

    /**
     * Office resolution goes through RideBookingRepository::findForUser, so
     * quoting somebody else's booking cannot attach the ticket to their office.
     */
    public function test_office_layer_ticket_on_foreign_booking_gets_no_office(): void
    {
        $b = $this->booking(7, 3);

        $id = $this->openTicket(8, 'lost_item', $b->id, 'not mine');

        $this->asUser(8)->getJson("user/tickets/{$id}")
            ->assertJsonPath('data.layer', 'office')
            ->assertJsonPath('data.office_id', null);
    }

    // ── ticket validation ───────────────────────────────────────────────────

    /**
     * The old contract took {category, subject, body}. OpenTicketRequest now
     * requires `topic` + `message`; the subject is synthesised from the topic,
     * so there is no separate subject to omit.
     */
    public function test_ticket_requires_topic_and_message(): void
    {
        $this->asUser()->postJson('user/tickets', ['topic' => 'refund'])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed');

        $this->asUser()->postJson('user/tickets', ['message' => 'x'])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed');
    }

    public function test_ticket_message_is_length_capped(): void
    {
        $this->asUser()->postJson('user/tickets', ['topic' => 'refund', 'message' => str_repeat('a', 2001)])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed');
    }

    // ── listing, reading, and rider scoping ─────────────────────────────────

    public function test_list_and_show_are_scoped_to_the_owner(): void
    {
        $id = $this->openTicket(7, 'refund', null, 'first');

        $this->asUser(7)->getJson('user/tickets')
            ->assertStatus(200)
            ->assertJsonPath('data.0.id', $id)
            ->assertJsonPath('data.0.topic', 'refund');

        // a stranger sees an empty list, and a direct read is 404 (not 403) —
        // the API will not confirm the ticket exists
        $this->asUser(8)->getJson('user/tickets')->assertStatus(200)->assertJsonPath('data', []);
        $this->asUser(8)->getJson("user/tickets/{$id}")
            ->assertStatus(404)
            ->assertJsonPath('error.code', 'not_found');
    }

    /** The opening message is persisted as the first thread entry. */
    public function test_show_returns_the_opening_message(): void
    {
        $id = $this->openTicket(7, 'refund', null, 'first');

        $show = $this->asUser(7)->getJson("user/tickets/{$id}")->assertStatus(200);

        $this->assertCount(1, $show->json('data.messages'));
        $this->assertSame('first', $show->json('data.messages.0.body'));
        $this->assertSame('user', $show->json('data.messages.0.sender_type'));
    }

    /**
     * NO LIVE ROUTE: the rider reply endpoint (`POST .../messages`) was never
     * re-registered under `user/`. RiderSupportService::reply is still the
     * implementation the eventual route will call, so its behaviour — append,
     * ownership check, reopen-on-reply — is pinned here instead, and the
     * resulting thread is verified through the live show endpoint.
     */
    public function test_rider_reply_appends_to_the_thread(): void
    {
        $id = $this->openTicket(7, 'refund', null, 'first');

        $this->support()->reply(7, $id, 'any update?');

        $show = $this->asUser(7)->getJson("user/tickets/{$id}")->assertStatus(200);
        $this->assertCount(2, $show->json('data.messages'));
        $this->assertSame('first', $show->json('data.messages.0.body'));
        $this->assertSame('any update?', $show->json('data.messages.1.body'));
    }

    public function test_rider_cannot_reply_to_a_foreign_ticket(): void
    {
        $id = $this->openTicket(7, 'refund', null, 'first');

        $this->expectException(DomainException::class);
        $this->support()->reply(8, $id, 'let me in');
    }

    /** A rider reply reopens a ticket staff had already resolved. */
    public function test_rider_reply_reopens_a_resolved_ticket(): void
    {
        $id = $this->openTicket(7, 'refund', null, 'first');
        $this->support()->setStatus($id, 'resolved');

        $this->assertSame('open', $this->support()->reply(7, $id, 'still broken')['status']);
    }

    // ── complaints (the live safety-report channel) ─────────────────────────

    /**
     * The old `api/v1/safety/report` is gone. Its live counterpart is
     * POST user/complaints, which writes a `complaints` row rather than a
     * support ticket and routes by `about`: a driver complaint goes to the
     * office.
     */
    public function test_driver_complaint_routes_to_the_office(): void
    {
        $b = $this->booking(7, 3);

        $this->asUser(7)->postJson('user/complaints', [
            'about' => 'driver', 'tripId' => $b->id, 'description' => 'reckless',
        ])->assertStatus(201)
            ->assertJsonPath('data.routed_to', 'office')
            ->assertJsonPath('data.priority', 'normal')
            ->assertJsonPath('data.status', 'open')
            ->assertJsonPath('data.case_ref', fn ($ref) => is_string($ref) && str_starts_with($ref, 'C-'));

        $complaint = Complaint::query()->first();
        $this->assertSame(7, (int) $complaint->user_id);
        $this->assertSame($b->id, (int) $complaint->booking_id);
    }

    /** `about=safety` escalates: FleetOS handles it, at urgent priority. */
    public function test_safety_complaint_is_urgent_and_goes_to_fleetos(): void
    {
        $this->asUser(7)->postJson('user/complaints', ['about' => 'safety', 'description' => 'felt unsafe'])
            ->assertStatus(201)
            ->assertJsonPath('data.routed_to', 'fleetos')
            ->assertJsonPath('data.priority', 'urgent');
    }

    public function test_complaint_requires_about_and_description(): void
    {
        $this->asUser()->postJson('user/complaints', ['about' => 'driver'])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed');

        $this->asUser()->postJson('user/complaints', ['description' => 'x'])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed');
    }

    // ── SOS (service level — no rider route) ────────────────────────────────

    /**
     * NO LIVE ROUTE: `api/v1/safety/sos` is dead and nothing replaced it on the
     * rider side (the driver app has driver/safety/sos; riders have nothing).
     * RiderSupportService::sos is intact, so the contract is pinned here: an
     * SOS is a fleetos-layer ticket AND a realtime event on the booking channel.
     */
    public function test_sos_creates_fleetos_ticket_and_event(): void
    {
        $b = $this->booking(7, 3);

        $ticket = $this->support()->sos(7, $b->id, 25.1, 51.2);

        $this->assertSame('fleetos', $ticket['layer']);
        $this->assertSame('sos', $ticket['category']);
        $this->assertNull($ticket['office_id']);

        $event = EventOutbox::query()->where('type', EventType::SUPPORT_MESSAGE_CREATED)->first();
        $this->assertNotNull($event);
        $this->assertContains('admin', $event->channels, 'fleet ops must hear a rider SOS');
        $this->assertContains('booking.' . $b->id, $event->channels);
        $this->assertContains('user.7', $event->channels);
    }

    /**
     * An SOS raised outside a trip still opens the ticket AND now publishes to
     * the fleet admin room + the rider — only the booking channel is dropped
     * (there is no trip). Previously a trip-less SOS notified nobody.
     */
    public function test_sos_without_booking_opens_ticket_and_alerts_admin(): void
    {
        $ticket = $this->support()->sos(7, null, 25.1, 51.2);

        $this->assertSame('sos', $ticket['category']);

        $event = EventOutbox::query()->where('type', EventType::SUPPORT_MESSAGE_CREATED)->first();
        $this->assertNotNull($event);
        $this->assertContains('admin', $event->channels);
        $this->assertContains('user.7', $event->channels);
    }

    // ── office call info (service level — no rider route) ───────────────────

    /**
     * NO LIVE ROUTE: `api/v1/support/office/call-info` is dead.
     * RiderSupportService::callInfo still enforces the important part — the
     * caller must own the booking, or it is notFound rather than a leak of
     * which office ran somebody else's trip.
     */
    public function test_call_info_requires_owned_booking(): void
    {
        $b = $this->booking(7, 5);

        $this->assertSame(5, $this->support()->callInfo(7, $b->id)['office_id']);

        $this->expectException(DomainException::class);
        $this->support()->callInfo(8, $b->id);
    }

    // ── trip sharing (service level — no rider route) ───────────────────────

    /**
     * NO LIVE ROUTE for minting the link: `user/bookings/{id}/share` is gone and
     * RideBookingService::share is unrouted. The PUBLIC side is still live
     * (`GET t/{slug}`), so this walks the whole loop: owner mints a URL, the
     * public page resolves it, and a stranger cannot mint one.
     */
    public function test_share_returns_url_for_owner(): void
    {
        $b = $this->booking(7, 3);

        $url = $this->rides()->share(7, $b->id)['share_url'];

        $this->assertIsString($url);
        $this->assertStringContainsString('/t/' . $b->id . '-', $url);

        // the minted slug resolves on the live public route
        $slug = substr($url, strpos($url, '/t/') + 3);
        $this->get('t/' . $slug)->assertStatus(200);
    }

    public function test_share_is_refused_for_a_foreign_booking(): void
    {
        $b = $this->booking(7, 3);

        $this->expectException(DomainException::class);
        $this->rides()->share(8, $b->id);
    }

    /** The share token is HMAC'd per booking, so a guessed slug 404s. */
    public function test_shared_trip_rejects_a_bad_token(): void
    {
        $b = $this->booking(7, 3);

        $this->get('t/' . $b->id . '-deadbeefdeadbeefdeadbeef')->assertStatus(404);
    }

    // ── staff side: office and fleet consoles ───────────────────────────────

    public function test_office_staff_reply_notifies_rider(): void
    {
        $b = $this->booking(7, 5);
        $ticket = $this->openTicket(7, 'lost_item', $b->id, 'left it');

        $support = $this->support();
        $this->assertSame($ticket, $support->officeTickets(5, null)[0]['ticket_id']);

        $result = $support->officeReply(5, $ticket, 5, 'We found it');
        $this->assertSame('open', $result['status']);

        $thread = $support->officeThread(5, $ticket);
        $this->assertSame('office', $thread['messages'][1]['from']);

        $notified = EventOutbox::query()->where('type', EventType::SUPPORT_MESSAGE_CREATED)->get()
            ->contains(fn ($e) => in_array('user.7', (array) $e->channels, true));
        $this->assertTrue($notified);
    }

    /** Office scoping: office 9 cannot read a ticket belonging to office 5. */
    public function test_office_cannot_touch_foreign_ticket(): void
    {
        $b = $this->booking(7, 5);
        $ticket = $this->openTicket(7, 'lost_item', $b->id, 'x');

        $this->expectException(DomainException::class);
        $this->support()->officeThread(9, $ticket);
    }

    /** Office scoping applies to writes too, not just reads. */
    public function test_office_cannot_reply_to_foreign_ticket(): void
    {
        $b = $this->booking(7, 5);
        $ticket = $this->openTicket(7, 'lost_item', $b->id, 'x');

        $this->expectException(DomainException::class);
        $this->support()->officeReply(9, $ticket, 9, 'sneaking in');
    }

    /**
     * A fleetos-layer ticket is invisible to every office console, even the one
     * that ran the trip — assertOfficeOwns checks the layer before the id.
     */
    public function test_office_cannot_see_fleetos_layer_ticket(): void
    {
        $b = $this->booking(7, 5);
        $ticket = $this->openTicket(7, 'refund', $b->id, 'overcharged');

        $this->assertSame([], $this->support()->officeTickets(5, null));

        $this->expectException(DomainException::class);
        $this->support()->officeThread(5, $ticket);
    }

    public function test_fleet_sees_sos_and_replies_and_sets_status(): void
    {
        $support = $this->support();
        $support->sos(7, null, 25.1, 51.2);

        $sos = $support->fleetTickets('open', 'sos');
        $this->assertCount(1, $sos);
        $this->assertSame('sos', $sos[0]['category']);
        $ticketId = $sos[0]['ticket_id'];

        $support->staffReply($ticketId, 'fleet', 1, 'Help is on the way');
        $this->assertSame('resolved', $support->setStatus($ticketId, 'resolved')['status']);
    }

    public function test_set_status_rejects_an_unknown_status(): void
    {
        $ticket = $this->openTicket(7, 'refund', null, 'x');

        $this->expectException(DomainException::class);
        $this->support()->setStatus($ticket, 'banana');
    }
}
