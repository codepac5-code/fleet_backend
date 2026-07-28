<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Dispatch\DispatchService;
use App\Http\Core\Const\Dispatch\DispatchStatus;
use App\Http\Core\Const\Dispatch\OfferStatus;
use App\Http\Core\Const\Dispatch\PresenceStatus;
use App\Models\DispatchJob;
use App\Models\DispatchOffer;
use Illuminate\Support\Facades\DB;

class DispatchTest extends FleetTestCase
{
    protected array $tenantMigrations = [
        '2026_06_25_000004_create_driver_presence_table.php',
        '2026_07_13_000003_add_busy_reason_to_driver_presence_table.php',
        '2026_06_25_000005_create_dispatch_jobs_table.php',
        '2026_06_25_000006_create_dispatch_offers_table.php',
        '2026_06_25_000007_create_event_outbox_table.php',
        // Offers are an append-only log so timed-out drivers can be re-offered.
        '2026_07_19_000001_allow_reoffer_on_dispatch_offers.php',
        // `accept` stamps the winning driver onto the booking.
        '2026_07_11_000001_create_ride_bookings_table.php',
        '2026_07_15_000001_add_rider_api_missing_columns.php', // adds driver_id
    ];

    private DispatchService $d;
    private float $lat = 25.2854;
    private float $lng = 51.5310;

    protected function setUp(): void
    {
        parent::setUp();
        $this->d = new DispatchService();
    }

    public function test_offer_wave_emits_offer_created_per_driver(): void
    {
        [$a, $an] = $this->near(300);
        [$b, $bn] = $this->near(900);
        $this->d->heartbeat(101, 3, PresenceStatus::ONLINE, $a, $an);
        $this->d->heartbeat(102, 3, PresenceStatus::ONLINE, $b, $bn);

        $dispatch = new DispatchService(new \App\Http\Core\Classes\Event\EventBus());
        $dispatch->createJob(7001, 3, 'standard', $this->lat, $this->lng);
        $dispatch->offerWave(7001, 20, 5000, 5);

        $events = \App\Models\EventOutbox::query()
            ->where('type', \App\Http\Core\Const\Event\EventType::DISPATCH_OFFER_CREATED)
            ->get();

        $this->assertCount(2, $events);
        $channels = $events->flatMap(fn ($e) => $e->channels)->all();
        $this->assertContains('driver.101', $channels);
        $this->assertContains('driver.102', $channels);
        // The owning office sees the offer wave live too (T8).
        $this->assertContains('office.3', $channels);
    }

    private function near(float $meters): array
    {
        return [$this->lat + ($meters / 111000.0), $this->lng];
    }

    public function test_candidates_are_nearest_online_in_office(): void
    {
        [$a, $an] = $this->near(300);
        [$b, $bn] = $this->near(900);
        [$c, $cn] = $this->near(1500);
        [$f, $fn] = $this->near(9000);
        $this->d->heartbeat(101, 3, PresenceStatus::ONLINE, $a, $an);
        $this->d->heartbeat(102, 3, PresenceStatus::ONLINE, $b, $bn);
        $this->d->heartbeat(103, 3, PresenceStatus::ONLINE, $c, $cn);
        $this->d->heartbeat(104, 3, PresenceStatus::OFFLINE, $a, $an);
        $this->d->heartbeat(105, 3, PresenceStatus::ONLINE, $f, $fn);
        $this->d->heartbeat(106, 4, PresenceStatus::ONLINE, $a, $an);

        $ids = array_map(fn ($c) => $c['driver_id'], $this->d->findCandidates(3, $this->lat, $this->lng, 5000, 10, 60));
        $this->assertSame([101, 102, 103], $ids);
    }

    public function test_concurrent_accept_assigns_exactly_one_driver(): void
    {
        [$a, $an] = $this->near(300);
        [$b, $bn] = $this->near(900);
        [$c, $cn] = $this->near(1500);
        $this->d->heartbeat(101, 3, PresenceStatus::ONLINE, $a, $an);
        $this->d->heartbeat(102, 3, PresenceStatus::ONLINE, $b, $bn);
        $this->d->heartbeat(103, 3, PresenceStatus::ONLINE, $c, $cn);

        $this->d->createJob(5001, 3, 'standard', $this->lat, $this->lng);
        $this->d->offerWave(5001, 20, 5000, 5, 60);

        $this->assertTrue($this->d->accept(5001, 102));
        $this->assertFalse($this->d->accept(5001, 101));
        $this->assertFalse($this->d->accept(5001, 103));

        $job = DispatchJob::query()->where('booking_id', 5001)->first();
        $this->assertSame(102, (int) $job->assigned_driver_id);
        $this->assertSame(DispatchStatus::ASSIGNED, $job->status);
        $this->assertSame(1, (int) DispatchOffer::query()->where('booking_id', 5001)->where('status', OfferStatus::ACCEPTED)->count());
        $this->assertSame(PresenceStatus::BUSY, DB::table('driver_presence')->where('driver_id', 102)->first()->status);
    }

    public function test_wave_advances_past_rejected_and_busy(): void
    {
        [$a, $an] = $this->near(300);
        [$b, $bn] = $this->near(900);
        $this->d->heartbeat(101, 3, PresenceStatus::ONLINE, $a, $an);
        $this->d->heartbeat(102, 3, PresenceStatus::ONLINE, $b, $bn);

        $this->d->createJob(5002, 3, 'standard', $this->lat, $this->lng);
        $w1 = $this->d->offerWave(5002, 20, 5000, 1, 60);
        $this->assertSame(101, (int) $w1[0]->driver_id);

        // Rejecting the last open offer advances the wave immediately rather
        // than burning the rest of the TTL, so 102 is offered without us having
        // to ask for another wave.
        $this->d->reject(5002, 101);

        $open = DispatchOffer::query()
            ->where('booking_id', 5002)
            ->where('status', OfferStatus::OFFERED)
            ->pluck('driver_id')
            ->map(fn ($i) => (int) $i)
            ->all();

        $this->assertSame([102], $open);
    }

    public function test_a_driver_who_timed_out_is_offered_again_but_a_rejecter_is_not(): void
    {
        [$a, $an] = $this->near(300);
        [$b, $bn] = $this->near(900);
        $this->d->heartbeat(101, 3, PresenceStatus::ONLINE, $a, $an);
        $this->d->heartbeat(102, 3, PresenceStatus::ONLINE, $b, $bn);

        $this->d->createJob(5010, 3, 'standard', $this->lat, $this->lng);
        $this->d->offerWave(5010, 20, 5000, 2, 60);

        // 101 says no outright; 102 simply never answers.
        $this->d->reject(5010, 101);
        DispatchOffer::query()
            ->where('booking_id', 5010)
            ->where('driver_id', 102)
            ->update(['status' => OfferStatus::EXPIRED]);

        $next = $this->d->offerWave(5010, 20, 5000, 5, 60);
        $offered = array_map(fn ($o) => (int) $o->driver_id, $next);

        // The one who ran out of time may have been mid-fare — ask again. The
        // one who declined is left alone.
        $this->assertSame([102], $offered);
    }

    public function test_stale_offers_cannot_be_accepted(): void
    {
        [$a, $an] = $this->near(300);
        $this->d->heartbeat(101, 3, PresenceStatus::ONLINE, $a, $an);
        $this->d->createJob(5003, 3, 'standard', $this->lat, $this->lng);
        $this->d->offerWave(5003, 1, 5000, 5, 60);
        DB::table('dispatch_offers')->where('booking_id', 5003)->update(['expires_at' => now()->subSeconds(5)]);
        $this->assertSame(1, $this->d->expireStaleOffers());
        $this->assertFalse($this->d->accept(5003, 101));
    }
}
