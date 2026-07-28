<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Dispatch\DispatchService;
use App\Http\Core\Const\Dispatch\OfferStatus;
use App\Http\Core\Const\Dispatch\PresenceStatus;
use App\Models\DispatchOffer;

class DispatchTickTest extends FleetTestCase
{
    protected array $tenantMigrations = [
        '2026_06_25_000004_create_driver_presence_table.php',
        '2026_07_13_000003_add_busy_reason_to_driver_presence_table.php',
        '2026_06_25_000005_create_dispatch_jobs_table.php',
        '2026_06_25_000006_create_dispatch_offers_table.php',
        // Dispatch always emits now (the event bus is no longer optional).
        '2026_06_25_000007_create_event_outbox_table.php',
        // Offers are an append-only log so timed-out drivers can be re-offered.
        '2026_07_19_000001_allow_reoffer_on_dispatch_offers.php',
    ];

    private DispatchService $d;
    private float $lat = 25.2854;
    private float $lng = 51.5310;

    protected function setUp(): void
    {
        parent::setUp();
        $this->d = new DispatchService();
    }

    private function near(float $meters): array
    {
        return [$this->lat + ($meters / 111000.0), $this->lng];
    }

    private function expireAllOffers(): void
    {
        DispatchOffer::query()->where('status', OfferStatus::OFFERED)->update(['expires_at' => now()->subMinute()]);
    }

    public function test_tick_advances_to_next_wave_excluding_already_offered(): void
    {
        [$a, $an] = $this->near(300);
        [$b, $bn] = $this->near(900);
        [$c, $cn] = $this->near(1500);
        $this->d->heartbeat(101, 3, PresenceStatus::ONLINE, $a, $an);
        $this->d->heartbeat(102, 3, PresenceStatus::ONLINE, $b, $bn);
        $this->d->heartbeat(103, 3, PresenceStatus::ONLINE, $c, $cn);

        $this->d->createJob(5001, 3, 'standard', $this->lat, $this->lng);
        $this->d->offerWave(5001, 20, 5000, 2);

        $this->assertEqualsCanonicalizing([101, 102], DispatchOffer::query()->pluck('driver_id')->map(fn ($i) => (int) $i)->all());

        $this->expireAllOffers();

        $result = $this->d->tick(20, 5000, 2);

        $this->assertSame(2, $result['expired']);
        $this->assertSame(1, $result['reoffered']);
        // The untouched third driver must be reached...
        $this->assertTrue(DispatchOffer::query()->where('driver_id', 103)->where('status', OfferStatus::OFFERED)->exists());
        // ...and a driver whose earlier offer merely timed out is eligible again,
        // so the second slot of this wave goes back to one of them rather than
        // being wasted.
        $reoffered = DispatchOffer::query()
            ->where('status', OfferStatus::OFFERED)
            ->pluck('driver_id')
            ->map(fn ($i) => (int) $i)
            ->all();
        $this->assertContains(103, $reoffered);
        $this->assertCount(2, $reoffered);
    }

    public function test_tick_does_not_reoffer_while_an_offer_is_still_active(): void
    {
        [$a, $an] = $this->near(300);
        $this->d->heartbeat(101, 3, PresenceStatus::ONLINE, $a, $an);

        $this->d->createJob(5002, 3, 'standard', $this->lat, $this->lng);
        $this->d->offerWave(5002, 60, 5000, 5);

        $result = $this->d->tick(60, 5000, 5);

        $this->assertSame(0, $result['expired']);
        $this->assertSame(0, $result['reoffered']);
    }

    public function test_tick_marks_job_exhausted_when_no_more_candidates(): void
    {
        [$a, $an] = $this->near(300);
        $this->d->heartbeat(101, 3, PresenceStatus::ONLINE, $a, $an);

        $this->d->createJob(5003, 3, 'standard', $this->lat, $this->lng);
        $this->d->offerWave(5003, 20, 5000, 5);

        // A timed-out driver would be asked again, so to genuinely exhaust the
        // pool the only candidate has to decline outright. `reject` already
        // tries to re-wave and finds nobody.
        $this->d->reject(5003, 101);

        $result = $this->d->tick(20, 5000, 5);

        $this->assertSame(0, $result['reoffered']);
        $this->assertSame(1, $result['exhausted']);
    }
}
