<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Support\RiderSupportService;
use App\Http\Core\Const\Support\SupportActor;
use App\Http\Core\Const\Support\SupportLayer;
use App\Http\Core\Const\Support\SupportStatus;
use App\Http\Core\Exceptions\DomainException;
use App\Models\RideBooking;

/**
 * Governance of the support-ticket lifecycle: only legal edges, only by an actor
 * with the authority, closed is terminal, and office→fleetos escalation is
 * governed.
 */
class SupportGovernanceTest extends FleetTestCase
{
    protected array $tenantMigrations = [
        '2026_06_25_000007_create_event_outbox_table.php',
        '2026_07_11_000001_create_ride_bookings_table.php',
        '2026_07_11_000004_add_titles_to_ride_bookings_table.php',
        '2026_07_11_000006_create_rider_support_tables.php',
        '2026_07_15_000001_add_rider_api_missing_columns.php',
    ];

    private function support(): RiderSupportService
    {
        return app(RiderSupportService::class);
    }

    /** A fleetos-layer ticket (category `refund`), no booking needed. */
    private function fleetTicket(): int
    {
        return (int) $this->support()->open(7, 'refund', null, 'Refund', 'please')['ticket_id'];
    }

    /** An office-layer ticket (category `past_trip` + a booking at office 3). */
    private function officeTicket(): int
    {
        $b = new RideBooking();
        $b->forceFill([
            'id' => 500, 'user_id' => 7, 'office_id' => 3, 'service' => 'ride', 'service_class' => 'standard',
            'pricing_style' => 'meter', 'status' => 'completed', 'currency_code' => 'SYP',
            'pickup_lat' => 33.5, 'pickup_lng' => 36.2, 'dropoff_lat' => 33.4, 'dropoff_lng' => 36.3,
            'fare_minor' => 1000, 'total_minor' => 1000,
        ])->save();

        return (int) $this->support()->open(7, 'past_trip', 500, 'Past', 'issue')['ticket_id'];
    }

    public function test_staff_can_run_legal_edges(): void
    {
        $id = $this->fleetTicket();

        $this->assertSame(SupportStatus::PENDING, $this->support()->setStatus($id, SupportStatus::PENDING, SupportActor::FLEETOS)['status']);
        $this->assertSame(SupportStatus::RESOLVED, $this->support()->setStatus($id, SupportStatus::RESOLVED, SupportActor::FLEETOS)['status']);
        $this->assertSame(SupportStatus::CLOSED, $this->support()->setStatus($id, SupportStatus::CLOSED, SupportActor::FLEETOS)['status']);
    }

    public function test_closed_is_terminal(): void
    {
        $id = $this->fleetTicket();
        $this->support()->setStatus($id, SupportStatus::CLOSED, SupportActor::FLEETOS);

        $this->expectException(DomainException::class);
        $this->support()->setStatus($id, SupportStatus::OPEN, SupportActor::FLEETOS); // CLOSED → OPEN illegal
    }

    public function test_rider_may_only_close_not_resolve(): void
    {
        $id = $this->fleetTicket();

        // A rider cannot mark their own ticket resolved.
        try {
            $this->support()->setStatus($id, SupportStatus::RESOLVED, SupportActor::RIDER);
            $this->fail('rider should not be able to resolve');
        } catch (DomainException $e) {
            // expected
        }

        // But a rider may close it.
        $this->assertSame(SupportStatus::CLOSED, $this->support()->setStatus($id, SupportStatus::CLOSED, SupportActor::RIDER)['status']);
    }

    public function test_same_status_is_an_idempotent_noop(): void
    {
        $id = $this->fleetTicket(); // starts OPEN
        $this->assertSame(SupportStatus::OPEN, $this->support()->setStatus($id, SupportStatus::OPEN, SupportActor::RIDER)['status']);
    }

    public function test_office_escalation_hands_the_ticket_to_fleetos(): void
    {
        $id = $this->officeTicket();

        $res = $this->support()->escalate($id, SupportActor::OFFICE, 'beyond us');

        $this->assertSame(SupportLayer::FLEETOS, $res['layer']);
        $this->assertNull($res['office_id']);
        $this->assertSame(SupportStatus::OPEN, $res['status']);
    }

    public function test_a_fleetos_ticket_cannot_be_escalated(): void
    {
        $id = $this->fleetTicket(); // already fleetos layer

        $this->expectException(DomainException::class);
        $this->support()->escalate($id, SupportActor::FLEETOS);
    }

    public function test_reply_to_a_closed_ticket_is_rejected(): void
    {
        $id = $this->fleetTicket();
        $this->support()->setStatus($id, SupportStatus::CLOSED, SupportActor::FLEETOS);

        $this->expectException(DomainException::class);
        $this->support()->reply(7, $id, 'reopen please');
    }
}
