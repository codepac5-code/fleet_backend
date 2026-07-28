<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Ops\HeartbeatService;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class HeartbeatServiceTest extends TestCase
{
    public function test_all_reports_every_known_daemon(): void
    {
        $daemons = (new HeartbeatService())->all();

        $this->assertCount(count(HeartbeatService::DAEMONS), $daemons);
        $this->assertSame(array_keys(HeartbeatService::DAEMONS), array_column($daemons, 'name'));
    }

    public function test_unseen_daemon_is_down(): void
    {
        $daemon = collect((new HeartbeatService())->all())->firstWhere('name', 'events-relay');

        $this->assertFalse($daemon['seen']);
        $this->assertFalse($daemon['up']);
        $this->assertNull($daemon['last']);
    }

    public function test_a_fresh_beat_reads_as_up(): void
    {
        $service = new HeartbeatService();
        $service->beat('events-relay');

        $daemon = collect($service->all())->firstWhere('name', 'events-relay');

        $this->assertTrue($daemon['seen']);
        $this->assertTrue($daemon['up']);
        $this->assertNotNull($daemon['last']);
        $this->assertLessThanOrEqual(2, $daemon['ago']);
    }

    public function test_a_stale_beat_reads_as_down(): void
    {
        // Write a heartbeat far older than the daemon's threshold.
        Cache::put('fleet:heartbeat:dispatch-tick', now()->subMinutes(10)->getTimestamp(), now()->addDay());

        $daemon = collect((new HeartbeatService())->all())->firstWhere('name', 'dispatch-tick');

        $this->assertTrue($daemon['seen']);
        $this->assertFalse($daemon['up'], 'a beat older than the threshold is down');
    }
}
