<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Support\LostFoundService;
use App\Http\Core\Const\LostItemStatus as St;
use App\Models\LostItem;

/**
 * Governance of the lost & found workflow: a rider reports LOST, a driver
 * reports FOUND, the office is auto-SUGGESTED the pair and CONFIRMS it, then
 * arbitrates the hand-back to `returned` (or `unresolved`). Every transition is
 * gated; a reporter can withdraw only while it is early.
 */
class LostFoundServiceTest extends FleetTestCase
{
    // lost_items lives on the GLOBAL connection (see the model).
    protected array $globalMigrations = [
        '2026_07_15_000001_add_rider_api_missing_columns.php',
        '2026_07_18_000001_add_photo_to_lost_items.php',
        '2026_07_23_000001_add_governance_to_lost_items.php',
        '2026_07_25_000005_add_country_to_support_tables.php',
    ];

    private LostFoundService $svc;

    protected function setUp(): void
    {
        parent::setUp();
        $this->svc = new LostFoundService();
    }

    private const OFFICE = 1;
    private const BOOKING = 900;

    private function rider(): LostItem
    {
        return $this->svc->reportLost(7, self::BOOKING, self::OFFICE, ['category' => 'Wallet', 'description' => 'brown leather']);
    }

    private function driver(): LostItem
    {
        return $this->svc->reportFound(33, 7, self::BOOKING, self::OFFICE, ['category' => 'wallet', 'description' => 'found under seat']);
    }

    public function test_reports_carry_their_reporter_side(): void
    {
        $r = $this->rider();
        $d = $this->driver();

        $this->assertSame(St::REPORTER_RIDER, $r->reporter_type);
        $this->assertSame(self::OFFICE, $r->office_id);
        $this->assertNull($r->driver_id);
        $this->assertSame(St::REPORTED, $r->status);

        $this->assertSame(St::REPORTER_DRIVER, $d->reporter_type);
        $this->assertSame(33, $d->driver_id);
    }

    public function test_opposite_side_reports_on_the_same_booking_are_suggested(): void
    {
        $r = $this->rider();
        $d = $this->driver();

        $forRider = $this->svc->suggestedMatches($r);
        $this->assertCount(1, $forRider);
        $this->assertSame((int) $d->id, (int) $forRider->first()->id);

        // A same-side report is NOT a suggestion.
        $r2 = $this->rider();
        $this->assertTrue($this->svc->suggestedMatches($r)->pluck('id')->doesntContain($r2->id));
    }

    public function test_office_confirms_a_match_and_both_sides_link(): void
    {
        $r = $this->rider();
        $d = $this->driver();

        $this->svc->confirmMatch(self::OFFICE, (int) $r->id, (int) $d->id);

        $r = $r->fresh();
        $d = $d->fresh();
        $this->assertSame(St::MATCHED, $r->status);
        $this->assertSame(St::MATCHED, $d->status);
        $this->assertSame((int) $d->id, (int) $r->matched_item_id);
        $this->assertSame((int) $r->id, (int) $d->matched_item_id);
        $this->assertNotNull($r->matched_at);
    }

    public function test_full_lifecycle_to_returned_closes_both_sides(): void
    {
        $r = $this->rider();
        $d = $this->driver();

        $this->svc->officeTransition(self::OFFICE, (int) $r->id, St::ACKNOWLEDGED);
        $this->svc->confirmMatch(self::OFFICE, (int) $r->id, (int) $d->id);
        $this->svc->officeTransition(self::OFFICE, (int) $r->id, St::READY);
        $this->svc->officeTransition(self::OFFICE, (int) $r->id, St::RETURNED, 'handed back at office');

        $r = $r->fresh();
        $d = $d->fresh();
        $this->assertSame(St::RETURNED, $r->status);
        $this->assertSame(St::RETURNED, $d->status, 'the matched pair closes together');
        $this->assertNotNull($r->returned_at);
        $this->assertSame('handed back at office', $r->resolution);
    }

    public function test_illegal_transition_is_rejected(): void
    {
        $r = $this->rider();
        // reported → returned skips the whole lifecycle.
        $this->expectExceptionMessage('invalid transition');
        $this->svc->officeTransition(self::OFFICE, (int) $r->id, St::RETURNED);
    }

    public function test_cannot_match_opposite_bookings_or_same_side(): void
    {
        $r = $this->rider();
        $other = $this->svc->reportFound(33, 7, 999, self::OFFICE, ['category' => 'phone']);
        try {
            $this->svc->confirmMatch(self::OFFICE, (int) $r->id, (int) $other->id);
            $this->fail('expected different-booking rejection');
        } catch (\Throwable $e) {
            $this->assertStringContainsString('match different booking', $e->getMessage());
        }

        $r2 = $this->rider();
        $this->expectExceptionMessage('match same side');
        $this->svc->confirmMatch(self::OFFICE, (int) $r->id, (int) $r2->id);
    }

    public function test_rider_can_withdraw_while_early(): void
    {
        $r = $this->rider();
        $this->svc->officeTransition(self::OFFICE, (int) $r->id, St::ACKNOWLEDGED);

        $this->svc->cancel(St::REPORTER_RIDER, 7, (int) $r->id);
        $this->assertSame(St::CANCELLED, $r->fresh()->status);
    }

    public function test_a_reporter_cannot_cancel_someone_elses_report(): void
    {
        $r = $this->rider();
        $this->expectExceptionMessage('lost item not found');
        $this->svc->cancel(St::REPORTER_RIDER, 8, (int) $r->id); // user 8 ≠ owner 7
    }

    public function test_cannot_cancel_once_matched(): void
    {
        $r = $this->rider();
        $d = $this->driver();
        $this->svc->confirmMatch(self::OFFICE, (int) $r->id, (int) $d->id);

        // The office owns the outcome once a match is confirmed.
        $this->expectExceptionMessage('not cancellable');
        $this->svc->cancel(St::REPORTER_RIDER, 7, (int) $r->id);
    }

    public function test_office_scope_is_enforced(): void
    {
        $r = $this->rider();
        $this->expectExceptionMessage('lost item not found');
        $this->svc->officeTransition(999, (int) $r->id, St::ACKNOWLEDGED); // wrong office
    }

    public function test_each_side_lists_only_its_own_reports_with_status(): void
    {
        $r = $this->rider();
        $d = $this->driver();

        $rider = $this->svc->forRider(7);
        $this->assertCount(1, $rider);
        $this->assertSame((int) $r->id, $rider[0]['id']);
        $this->assertSame(St::REPORTER_RIDER, $rider[0]['reporter_type']);
        $this->assertSame(St::REPORTED, $rider[0]['status']);
        $this->assertTrue($rider[0]['cancellable']);
        $this->assertFalse($rider[0]['is_matched']);

        $driver = $this->svc->forDriver(33);
        $this->assertCount(1, $driver);
        $this->assertSame((int) $d->id, $driver[0]['id']);
        $this->assertSame(St::REPORTER_DRIVER, $driver[0]['reporter_type']);

        // A driver never sees the rider's lost report in their own list.
        $this->assertTrue(collect($driver)->pluck('id')->doesntContain($r->id));
    }
}
