<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Dispatch\DispatchService;
use App\Http\Core\Classes\Event\EventBus;
use App\Http\Core\Classes\Event\EventPublisher;
use App\Http\Core\Classes\Ledger\FleetWalletService;
use App\Http\Core\Classes\Pricing\PricingService;
use App\Http\Core\Classes\Pricing\TariffResolver;
use App\Http\Core\Classes\Ride\DriverTripService;
use App\Http\Core\Classes\Ride\RideLifecycleService;
use App\Http\Core\Const\Dispatch\DispatchStatus;
use App\Http\Core\Repositories\Dispatch\DispatchJobRepository;
use App\Http\Core\Repositories\Ride\RideBookingRepository;
use App\Models\DispatchJob;
use App\Models\EventOutbox;
use App\Models\RideBooking;

class DriverLocationRealtimeTest extends FleetTestCase
{
    protected array $tenantMigrations = [
        '2026_06_25_000005_create_dispatch_jobs_table.php',
        '2026_06_25_000007_create_event_outbox_table.php',
        '2026_07_01_000002_create_service_tariffs_table.php',
        '2026_07_11_000002_add_service_to_service_tariffs_table.php',
        '2026_07_11_000001_create_ride_bookings_table.php',
        '2026_07_11_000004_add_titles_to_ride_bookings_table.php',
        '2026_07_11_000008_add_schedule_to_ride_bookings_table.php',
        '2026_07_11_000009_add_change_revision_to_ride_bookings_table.php',
    ];

    public function test_location_publishes_direct_to_realtime_without_touching_the_outbox(): void
    {
        DispatchJob::query()->create([
            'booking_id' => 900, 'office_id' => 3, 'service_class' => 'standard',
            'lat' => 25.1, 'lng' => 51.2, 'status' => DispatchStatus::ASSIGNED,
            'assigned_driver_id' => 9, 'wave' => 1, 'assigned_at' => now(),
        ]);

        RideBooking::query()->create([
            'user_id' => 7, 'office_id' => 3, 'service' => 'ride', 'service_class' => 'standard',
            'pricing_style' => 'meter', 'status' => 'on_trip',
            'pickup_lat' => 25.1, 'pickup_lng' => 51.2, 'dropoff_lat' => 25.2, 'dropoff_lng' => 51.3,
            'currency_code' => 'USD', 'fare_minor' => 10000, 'total_minor' => 10000,
        ])->forceFill(['id' => 900])->save();

        $spy = new class implements EventPublisher {
            public array $calls = [];

            public function publish(string $channel, string $type, array $payload): void
            {
                $this->calls[] = ['channel' => $channel, 'type' => $type, 'payload' => $payload];
            }
        };

        $service = new DriverTripService(
            app(RideBookingRepository::class),
            app(DispatchJobRepository::class),
            app(RideLifecycleService::class),
            app(FleetWalletService::class),
            app(DispatchService::class),
            app(TariffResolver::class),
            app(PricingService::class),
            new EventBus(),
            $spy
        );

        $service->updateLocation(9, 900, 25.15, 51.25);

        // A metered on-trip ping publishes BOTH the driver location and the live
        // meter, to the booking and user channels — all direct to realtime.
        $this->assertCount(4, $spy->calls);
        $this->assertSame('booking.900', $spy->calls[0]['channel']);
        $this->assertSame('user.7', $spy->calls[1]['channel']);
        $this->assertSame('driver.location', $spy->calls[0]['type']);
        $this->assertSame(25.15, $spy->calls[0]['payload']['lat']);

        $meterCalls = array_values(array_filter($spy->calls, fn ($c) => $c['type'] === 'booking.meter'));
        $this->assertCount(2, $meterCalls);
        $this->assertArrayHasKey('distance_m', $meterCalls[0]['payload']);
        $this->assertArrayHasKey('fare_minor', $meterCalls[0]['payload']);

        // None of it touches the durable outbox — it's ephemeral live data.
        $this->assertSame(0, EventOutbox::query()->count());
    }
}
