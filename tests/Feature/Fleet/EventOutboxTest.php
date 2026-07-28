<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Dispatch\DispatchService;
use App\Http\Core\Classes\Event\CollectingPublisher;
use App\Http\Core\Classes\Event\DomainEvent;
use App\Http\Core\Classes\Event\EventBus;
use App\Http\Core\Classes\Event\EventPublisher;
use App\Http\Core\Classes\Event\OutboxRelay;
use App\Http\Core\Const\Dispatch\PresenceStatus;
use App\Http\Core\Const\Event\Channel;
use App\Http\Core\Const\Event\EventStatus;
use App\Http\Core\Const\Event\EventType;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class EventOutboxTest extends FleetTestCase
{
    protected array $tenantMigrations = [
        '2026_06_25_000004_create_driver_presence_table.php',
        '2026_07_13_000003_add_busy_reason_to_driver_presence_table.php',
        '2026_06_25_000005_create_dispatch_jobs_table.php',
        '2026_06_25_000006_create_dispatch_offers_table.php',
        '2026_06_25_000007_create_event_outbox_table.php',
        '2026_07_19_000001_allow_reoffer_on_dispatch_offers.php',
        // `accept` stamps the winning driver onto the booking.
        '2026_07_11_000001_create_ride_bookings_table.php',
        '2026_07_15_000001_add_rider_api_missing_columns.php', // adds driver_id
    ];

    public function test_assignment_emits_event_relay_publishes_once(): void
    {
        $bus = new EventBus();
        $d = new DispatchService($bus);
        $lat = 25.2854; $lng = 51.5310;
        $d->heartbeat(9, 3, PresenceStatus::ONLINE, $lat + 0.002, $lng);
        $d->createJob(6001, 3, 'standard', $lat, $lng);
        $d->offerWave(6001, 20, 5000, 5, 60);
        $this->assertTrue($d->accept(6001, 9));

        $this->assertSame(1, (int) DB::table('event_outbox')->where('type', EventType::DISPATCH_RIDE_ASSIGNED)->count());
        $this->assertSame(1, (int) DB::table('event_outbox')->where('type', EventType::DISPATCH_OFFER_CREATED)->count());

        $this->assertSame(1, (int) DB::table('event_outbox')->where('type', EventType::PRESENCE_CHANGED)->count());

        $pub = new CollectingPublisher();
        $relay = new OutboxRelay($pub);
        $this->assertSame(3, $relay->publishPending()['published']);
        // 7 channel-publishes: RIDE_ASSIGNED (booking+driver+office=3) +
        // PRESENCE_CHANGED (driver+office=2) + DISPATCH_OFFER_CREATED
        // (driver+office=2, the office added in T8).
        $this->assertSame(7, $pub->count());
        $this->assertSame(0, $relay->publishPending()['published']);
        $this->assertSame(7, $pub->count());
    }

    public function test_failing_publish_retries_then_fails(): void
    {
        $bus = new EventBus();
        $bus->emit(new DomainEvent(EventType::WALLET_CREDITED, [Channel::user(7)], ['amount' => 'x']));

        $failing = new class implements EventPublisher {
            public function publish(string $channel, string $type, array $payload): void
            {
                throw new RuntimeException('down');
            }
        };
        $relay = new OutboxRelay($failing);
        $relay->publishPending(100, 3, 0);
        $this->assertSame(EventStatus::PENDING, DB::table('event_outbox')->first()->status);
        $relay->publishPending(100, 3, 0);
        $relay->publishPending(100, 3, 0);
        $this->assertSame(EventStatus::FAILED, DB::table('event_outbox')->first()->status);
    }

    public function test_rollback_emits_no_phantom_event(): void
    {
        $bus = new EventBus();
        $before = (int) DB::table('event_outbox')->count();

        try {
            DB::connection(DB::getDefaultConnection())->transaction(function () use ($bus) {
                $bus->emit(new DomainEvent(EventType::RIDE_RELEASED, [Channel::booking(999)], []));
                throw new RuntimeException('rollback');
            });
        } catch (RuntimeException $e) {
        }

        $this->assertSame($before, (int) DB::table('event_outbox')->count());
    }
}
