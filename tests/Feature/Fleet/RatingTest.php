<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Event\EventBus;
use App\Http\Core\Classes\Rating\RatingService;
use App\Http\Core\Const\Event\EventType;
use App\Http\Core\Repositories\Rating\EloquentRideRatingRepository;
use App\Models\Driver;
use App\Models\EventOutbox;
use App\Models\Office;
use RuntimeException;

class RatingTest extends FleetTestCase
{
    protected array $tenantMigrations = [
        '2024_10_29_211028_create_offices_table.php',
        '2024_11_6_212751_create_vehicles_table.php',
        '2024_11_10_085532_create_drivers_table.php',
        '2026_06_25_000007_create_event_outbox_table.php',
        '2026_06_25_000017_create_ride_ratings_table.php',
    ];

    private RatingService $ratings;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ratings = new RatingService(new EloquentRideRatingRepository(), new EventBus());
    }

    public function test_new_rating_emits_rating_received_to_ratee(): void
    {
        $this->ratings->rate(5001, 'user', 7, 'driver', 9, 5);

        $event = EventOutbox::query()->where('type', EventType::RATING_RECEIVED)->first();

        $this->assertNotNull($event);
        $this->assertContains('driver.9', $event->channels);
    }

    public function test_a_driver_rating_also_reaches_the_office_and_the_fleet_desk(): void
    {
        // A rating used to go ONLY to whoever was rated, so a one-star driver
        // was known to that driver alone — the office responsible for them and
        // the quality desk never heard it.
        $office = Office::query()->create([
            'officeName' => 'Al Fleet', 'email' => 'o@x.qa', 'password' => 'x',
            'contactNumber' => '33001234', 'address' => 'West Bay', 'country' => 'QA',
            'city' => 'Doha', 'region' => 'Doha', 'status' => 1,
        ]);

        Driver::query()->create([
            'firstName' => 'Sami', 'lastName' => 'Nour', 'password' => 'x',
            'phoneNumber' => '55500600', 'dialCode' => '+974', 'officeId' => $office->id,
            'address' => 'Al Sadd', 'country' => 'QA', 'city' => 'Doha', 'region' => 'Doha',
        ]);

        $this->ratings->rate(5003, 'user', 7, 'driver', 1, 1);

        $event = EventOutbox::query()->where('type', EventType::RATING_RECEIVED)->latest('id')->first();

        $this->assertContains('driver.1', $event->channels);
        $this->assertContains('office.' . $office->id, $event->channels);
        $this->assertContains('admin', $event->channels);
        $this->assertSame(1, $event->payload['stars']);
    }

    public function test_average_aggregates_across_bookings(): void
    {
        $this->ratings->rate(5001, 'user', 7, 'driver', 9, 5);
        $this->ratings->rate(5002, 'user', 8, 'driver', 9, 3);

        $summary = $this->ratings->summaryFor('driver', 9);

        $this->assertSame(2, $summary['count']);
        $this->assertSame(4.0, $summary['average']);
    }

    public function test_rating_is_one_per_direction_per_booking(): void
    {
        $this->ratings->rate(5001, 'user', 7, 'driver', 9, 5);
        $this->ratings->rate(5001, 'user', 7, 'driver', 9, 1);

        $this->assertSame(1, $this->ratings->summaryFor('driver', 9)['count']);
        $this->assertSame(5.0, $this->ratings->summaryFor('driver', 9)['average']);
    }

    public function test_both_directions_coexist_on_same_booking(): void
    {
        $this->ratings->rate(5001, 'user', 7, 'driver', 9, 5);
        $this->ratings->rate(5001, 'driver', 9, 'user', 7, 4);

        $this->assertCount(2, $this->ratings->forBooking(5001));
        $this->assertSame(4.0, $this->ratings->summaryFor('user', 7)['average']);
    }

    public function test_cannot_rate_yourself(): void
    {
        $this->expectException(RuntimeException::class);
        $this->ratings->rate(5001, 'driver', 9, 'driver', 9, 5);
    }

    public function test_stars_out_of_range_rejected(): void
    {
        $this->expectException(RuntimeException::class);
        $this->ratings->rate(5001, 'user', 7, 'driver', 9, 6);
    }

    public function test_rating_feed_admin_and_office_scope(): void
    {
        $this->ratings->rate(6001, 'user', 7, 'driver', 9, 5);
        $this->ratings->rate(6002, 'user', 8, 'driver', 9, 2);
        $this->ratings->rate(6003, 'user', 8, 'office', 3, 1);

        $repo = new EloquentRideRatingRepository();
        $this->assertCount(2, $repo->feedAll(null, 2, 50));
        $this->assertCount(2, $repo->feedAll('driver', null, 50));
        $this->assertCount(3, $repo->feedForOfficeScope(3, [9], null, 50));

        $feed = app(\App\Http\Core\Classes\Rating\RatingFeedService::class)->adminFeed('office', null, 50);
        $this->assertSame('office', $feed[0]['ratee_type']);
        $this->assertSame(1, $feed[0]['stars']);
    }
}
