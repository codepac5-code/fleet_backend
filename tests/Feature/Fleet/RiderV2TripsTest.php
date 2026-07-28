<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Const\Dispatch\DispatchStatus;
use App\Models\DispatchJob;
use App\Models\EventOutbox;
use App\Models\FavoriteOffice;
use App\Models\LostItem;
use App\Models\Office;
use App\Models\RideBooking;
use App\Models\RideRating;
use App\Models\RiderSupportTicket;
use App\Models\User;

class RiderV2TripsTest extends FleetTestCase
{
    protected array $globalMigrations = [
        '2026_07_15_000001_add_rider_api_missing_columns.php',
        '2026_07_25_000005_add_country_to_support_tables.php',
        '2026_07_18_000001_add_photo_to_lost_items.php',
        '2026_07_23_000001_add_governance_to_lost_items.php',
    ];

    protected array $tenantMigrations = [
        '2024_10_29_211028_create_offices_table.php',
        '2026_06_24_000004_create_commission_snapshots_table.php',
        '2026_06_25_000005_create_dispatch_jobs_table.php',
        '2026_06_25_000006_create_dispatch_offers_table.php',
        '2026_06_25_000007_create_event_outbox_table.php',
        '2026_06_25_000012_create_favorite_offices_table.php',
        '2026_06_25_000017_create_ride_ratings_table.php',
        '2026_07_11_000001_create_ride_bookings_table.php',
        '2026_07_11_000004_add_titles_to_ride_bookings_table.php',
        '2026_07_11_000005_create_booking_chat_messages_table.php',
        '2026_07_11_000006_create_rider_support_tables.php',
        '2026_07_11_000008_add_schedule_to_ride_bookings_table.php',
        '2026_07_11_000009_add_change_revision_to_ride_bookings_table.php',
        '2026_07_14_000001_add_office_booking_fields_to_ride_bookings.php',
        '2026_07_15_000001_add_rider_api_missing_columns.php',
        '2026_07_25_000005_add_country_to_support_tables.php',
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
            'city' => 'Doha', 'region' => 'Doha', 'status' => 1, 'is_verified' => true, 'lat' => 25.28, 'lng' => 51.53,
        ]);
    }

    private function booking(int $userId, int $officeId, string $status, array $extra = []): RideBooking
    {
        return RideBooking::query()->create(array_merge([
            'user_id' => $userId, 'office_id' => $officeId, 'source' => 'rider',
            'service' => 'ride', 'service_class' => 'standard', 'pricing_style' => 'fixed',
            'status' => $status, 'pickup_lat' => 25.28, 'pickup_lng' => 51.53, 'pickup_title' => 'A',
            'dropoff_lat' => 25.27, 'dropoff_lng' => 51.60, 'dropoff_title' => 'B',
            'distance_m' => 5400, 'duration_s' => 720, 'currency_code' => 'USD',
            'fare_minor' => 5000, 'total_minor' => 5000, 'payment_method' => 'cash',
        ], $extra));
    }

    private function assign(int $bookingId, int $officeId, int $driverId, string $status = DispatchStatus::ASSIGNED): void
    {
        DispatchJob::query()->create([
            'booking_id' => $bookingId, 'office_id' => $officeId, 'service_class' => 'standard',
            'lat' => 25.28, 'lng' => 51.53, 'status' => $status, 'wave' => 1,
            'assigned_driver_id' => $driverId, 'assigned_at' => now(),
        ]);
    }

    public function test_history_filters_active_and_past(): void
    {
        $office = $this->office();
        $active = $this->booking(7, $office->id, 'matching');
        $done = $this->booking(7, $office->id, 'completed');

        $this->asUser()->getJson('user/trips?status=active')
            ->assertStatus(200)
            ->assertJsonPath('data.items.0.id', $active->id)
            ->assertJsonPath('data.items.0.office.officeName', 'Al Fleet')
            ->assertJsonCount(1, 'data.items');

        $this->asUser()->getJson('user/trips?status=past')
            ->assertStatus(200)
            ->assertJsonPath('data.items.0.id', $done->id)
            ->assertJsonCount(1, 'data.items');
    }

    public function test_show_detail_returns_flat_row_office_and_rating_null(): void
    {
        $office = $this->office();
        $b = $this->booking(7, $office->id, 'completed');

        $this->asUser()->getJson("user/trips/{$b->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.id', $b->id)
            ->assertJsonPath('data.office.officeName', 'Al Fleet')
            ->assertJsonPath('data.rating', null);

        $this->asUser(8)->getJson("user/trips/{$b->id}")->assertStatus(404);
    }

    public function test_rate_trip_dual_rating_and_events(): void
    {
        $office = $this->office();
        $b = $this->booking(7, $office->id, 'completed');
        $this->assign($b->id, $office->id, 101);

        $this->asUser()->postJson("user/trips/{$b->id}/rating", [
            'stars' => 5, 'tags' => ['clean', 'polite'], 'comment' => 'great', 'bookAgain' => true, 'favorite' => true,
        ])->assertStatus(200)->assertJsonPath('data.ok', true);

        $this->assertNotNull($b->fresh()->rated_at);
        $this->assertTrue(RideRating::query()->where('booking_id', $b->id)->where('ratee_type', 'driver')->exists());
        $this->assertTrue(RideRating::query()->where('booking_id', $b->id)->where('ratee_type', 'office')->exists());
        $this->assertTrue(FavoriteOffice::query()->where('user_id', 7)->where('office_id', $office->id)->exists());

        $driverRating = RideRating::query()->where('booking_id', $b->id)->where('ratee_type', 'driver')->first();
        $this->assertSame(['clean', 'polite'], $driverRating->tags);
        $this->assertTrue((bool) $driverRating->book_again);

        $this->assertSame(2, EventOutbox::query()->where('type', 'rating.received')->count());
    }

    public function test_chat_send_and_list(): void
    {
        $office = $this->office();
        $b = $this->booking(7, $office->id, 'on_trip');
        $this->assign($b->id, $office->id, 101);

        $this->asUser()->postJson("user/trips/{$b->id}/messages", ['body' => 'I am at the gate'])
            ->assertStatus(201)
            ->assertJsonPath('data.from_type', 'rider')
            ->assertJsonPath('data.booking_id', $b->id)
            ->assertJsonPath('data.body', 'I am at the gate');

        $this->assertSame(1, EventOutbox::query()->where('type', 'booking.chat_message')->count());

        $this->asUser()->getJson("user/trips/{$b->id}/messages")
            ->assertStatus(200)
            ->assertJsonPath('data.items.0.body', 'I am at the gate')
            ->assertJsonPath('data.items.0.from_type', 'rider');
    }

    public function test_lost_item_opens_ticket(): void
    {
        $office = $this->office();
        $b = $this->booking(7, $office->id, 'completed');

        $res = $this->asUser()->postJson("user/trips/{$b->id}/lost-item", [
            'category' => 'Phone', 'description' => 'Black iPhone', 'shareMaskedNumber' => true,
        ])->assertStatus(201);

        $this->assertNotNull($res->json('data.ticketId'));
        $this->assertTrue(LostItem::query()->where('booking_id', $b->id)->where('category', 'Phone')->exists());
        $this->assertTrue(RiderSupportTicket::query()->where('user_id', 7)->where('category', 'lost_item')->exists());
    }

    public function test_rate_validation_rejects_bad_stars(): void
    {
        $office = $this->office();
        $b = $this->booking(7, $office->id, 'completed');

        $this->asUser()->postJson("user/trips/{$b->id}/rating", ['stars' => 9])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed');
    }
}
