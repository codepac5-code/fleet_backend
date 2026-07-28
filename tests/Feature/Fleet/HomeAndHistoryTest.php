<?php

namespace Tests\Feature\Fleet;

use App\Models\Office;
use App\Models\RideBooking;
use App\Models\User;

/**
 * Trip history — the rider's "my trips" list.
 *
 * `GET user/bookings?status=…` is gone; the live endpoint is `GET user/trips`.
 * Contract shifts the old assertions encoded incorrectly:
 *
 *  - the payload is PAGINATED: `data.items` + `data.nextCursor`. There is no
 *    top-level `meta.has_more` / `meta.next_cursor` any more; "has more" is
 *    expressed as a non-null `nextCursor`.
 *  - rows are BookingPresenter::listRow() — the full flat booking row plus an
 *    embedded `office` card. So the destination is `dropoff_title`, not `to`,
 *    and there is no `rating_state` field (whether a trip was rated is carried
 *    by the `rated_at` timestamp).
 *  - the status filter collapses to exactly three buckets in
 *    TripService::history(): 'completed'|'past' -> completed, 'cancelled' ->
 *    cancelled, and ANY other value (including an unknown one, or none at all)
 *    -> active. So the old `status=upcoming` still works, but only because it
 *    falls through to the active bucket — not because it is recognised.
 */
class HomeAndHistoryTest extends FleetTestCase
{
    protected array $globalMigrations = [
        '2026_07_11_000003_create_saved_places_table.php',
        '2026_07_15_000001_add_rider_api_missing_columns.php',
    ];

    protected array $tenantMigrations = [
        '2024_10_29_211028_create_offices_table.php',
        '2026_06_24_000001_create_ledger_accounts_table.php',
        '2026_06_24_000002_create_ledger_transactions_table.php',
        '2026_06_24_000003_create_ledger_entries_table.php',
        '2026_06_24_000004_create_commission_snapshots_table.php',
        '2026_06_25_000005_create_dispatch_jobs_table.php',
        '2026_06_25_000012_create_favorite_offices_table.php',
        '2026_06_25_000017_create_ride_ratings_table.php',
        '2026_07_11_000001_create_ride_bookings_table.php',
        '2026_07_11_000004_add_titles_to_ride_bookings_table.php',
        '2026_07_11_000008_add_schedule_to_ride_bookings_table.php',
        '2026_07_11_000009_add_change_revision_to_ride_bookings_table.php',
        '2026_07_14_000001_add_office_booking_fields_to_ride_bookings.php',
        '2026_07_15_000001_add_rider_api_missing_columns.php',
    ];

    private function asUser(int $id = 7): self
    {
        $user = new User();
        $user->id = $id;

        return $this->actingAs($user, 'user');
    }

    private function office(): Office
    {
        return Office::query()->create([
            'officeName' => 'Al Fleet', 'email' => 'o@x.qa', 'password' => 'x',
            'contactNumber' => '33001234', 'address' => 'West Bay, Doha', 'country' => 'QA',
            'city' => 'Doha', 'region' => 'Doha', 'status' => 1, 'is_verified' => true,
            'lat' => 25.28, 'lng' => 51.53,
        ]);
    }

    private function booking(int $userId, int $officeId, string $status, string $to = 'Airport'): RideBooking
    {
        return RideBooking::query()->create([
            'user_id' => $userId, 'office_id' => $officeId, 'service' => 'travel', 'service_class' => 'standard',
            'pricing_style' => 'fixed', 'status' => $status,
            'pickup_lat' => 25.28, 'pickup_lng' => 51.53, 'pickup_title' => 'Al Sadd',
            'dropoff_lat' => 25.27, 'dropoff_lng' => 51.60, 'dropoff_title' => $to,
            'currency_code' => 'USD', 'fare_minor' => 5500, 'discount_minor' => 500, 'total_minor' => 5000,
        ]);
    }

    public function test_history_completed_filter(): void
    {
        $office = $this->office();
        $done = $this->booking(7, $office->id, 'completed', 'Done Trip');
        $this->booking(7, $office->id, 'matching', 'Active Trip');

        $res = $this->asUser()->getJson('user/trips?status=completed')->assertStatus(200);

        $this->assertCount(1, $res->json('data.items'));
        $this->assertSame($done->id, $res->json('data.items.0.id'));
        $this->assertSame('Done Trip', $res->json('data.items.0.dropoff_title'));
        // an unrated trip carries a null `rated_at` (the old `rating_state` is gone)
        $this->assertNull($res->json('data.items.0.rated_at'));
        $this->assertSame('Al Fleet', $res->json('data.items.0.office.officeName'));
    }

    /** `past` is the documented alias for `completed`. */
    public function test_history_past_is_an_alias_for_completed(): void
    {
        $office = $this->office();
        $done = $this->booking(7, $office->id, 'completed', 'Done Trip');
        $this->booking(7, $office->id, 'matching');

        $this->asUser()->getJson('user/trips?status=past')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.id', $done->id);
    }

    public function test_history_upcoming_and_cancelled_filters(): void
    {
        $office = $this->office();
        $this->booking(7, $office->id, 'matching');
        $this->booking(7, $office->id, 'cancelled');

        // `upcoming` is not a recognised bucket — it falls through to `active`.
        $this->asUser()->getJson('user/trips?status=upcoming')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.status', 'matching');

        $this->asUser()->getJson('user/trips?status=cancelled')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.status', 'cancelled');
    }

    /** No status at all is the same as `active`. */
    public function test_history_defaults_to_active(): void
    {
        $office = $this->office();
        $active = $this->booking(7, $office->id, 'matching');
        $this->booking(7, $office->id, 'completed');
        $this->booking(7, $office->id, 'cancelled');

        $this->asUser()->getJson('user/trips')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.id', $active->id);
    }

    /**
     * Pagination is cursor-based: `nextCursor` is non-null only while more rows
     * remain, and following it walks newest-first without repeating a row.
     */
    public function test_history_pagination_cursor(): void
    {
        $office = $this->office();
        $ids = [];
        foreach (range(1, 3) as $i) {
            $ids[] = $this->booking(7, $office->id, 'completed')->id;
        }
        rsort($ids); // the list is ordered by id descending

        $page1 = $this->asUser()->getJson('user/trips?status=completed&limit=2')->assertStatus(200);

        $this->assertCount(2, $page1->json('data.items'));
        $this->assertSame([$ids[0], $ids[1]], array_column($page1->json('data.items'), 'id'));

        $cursor = $page1->json('data.nextCursor');
        $this->assertNotNull($cursor);

        $page2 = $this->asUser()->getJson('user/trips?status=completed&limit=2&cursor=' . urlencode($cursor))
            ->assertStatus(200);

        $this->assertCount(1, $page2->json('data.items'));
        $this->assertSame($ids[2], $page2->json('data.items.0.id'));
        // last page: nothing left to fetch
        $this->assertNull($page2->json('data.nextCursor'));
    }

    /** History is per-rider — user B's trips never appear in user A's list. */
    public function test_history_is_scoped_to_the_caller(): void
    {
        $office = $this->office();
        $mine = $this->booking(7, $office->id, 'completed', 'Mine');
        $this->booking(8, $office->id, 'completed', 'Theirs');

        $this->asUser(7)->getJson('user/trips?status=completed')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.id', $mine->id)
            ->assertJsonPath('data.items.0.dropoff_title', 'Mine');
    }

    /**
     * GAP: no receipt endpoint.
     *
     * `GET user/bookings/{id}/receipt` was removed and nothing replaced it — no
     * live route returns a fare breakdown. `GET user/trips/{id}` is the closest
     * thing, but it returns the flat booking row (fare_minor / discount_minor /
     * total_minor as scalars) with NO itemised `fare_breakdown`, so it does not
     * satisfy the original intent.
     *
     * Skipped rather than deleted, and deliberately NOT re-pointed at
     * `user/trips/{id}`: inventing a breakdown assertion against a row that has
     * none would be phantom coverage of exactly the kind this repair removed.
     * Ownership scoping for trip detail IS covered (RiderV2TripsTest).
     */
    public function test_receipt_breakdown_and_owner(): void
    {
        $this->markTestSkipped(
            'No live route serves a receipt: GET user/bookings/{id}/receipt was removed and has no '
            . 'replacement. GET user/trips/{id} returns scalar fare/discount/total with no '
            . 'itemised fare_breakdown, so it cannot stand in.'
        );
    }
}
