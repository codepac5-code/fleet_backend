<?php

namespace App\Console\Commands;

use App\Http\Core\Classes\Dispatch\DispatchService;
use App\Http\Core\Classes\Ops\HeartbeatService;
use App\Http\Core\Classes\Ride\RideBookingService;
use App\Http\Core\GeoServices\ShardRunner;
use Illuminate\Console\Command;
use Throwable;

class DispatchTick extends Command
{
    // --radius omitted → progressive per-wave radius (dispatch_radius_m widening
    // by dispatch_radius_step_m up to dispatch_radius_max_m). Pass it only to pin
    // a fixed radius for debugging.
    protected $signature = 'fleet:dispatch-tick {--ttl=20} {--radius=} {--limit=5} {--daemon} {--sleep=3}';

    protected $description = 'Expire stale dispatch offers and advance offer waves for unassigned jobs, per shard';

    public function handle(): int
    {
        $ttl = (int) $this->option('ttl');
        $radius = $this->option('radius') === null || $this->option('radius') === ''
            ? null
            : (float) $this->option('radius');
        $limit = (int) $this->option('limit');

        if ($this->option('daemon')) {
            $sleep = max(1, (int) $this->option('sleep'));

            while (true) {
                app(HeartbeatService::class)->beat('dispatch-tick');
                $this->tickAllShards($ttl, $radius, $limit);
                sleep($sleep);
            }
        }

        $this->tickAllShards($ttl, $radius, $limit);

        return self::SUCCESS;
    }

    private function tickAllShards(int $ttl, ?float $radius, int $limit): void
    {
        ShardRunner::eachCountry(function ($node) use ($ttl, $radius, $limit) {
            $dispatch = app(DispatchService::class);
            $result = $dispatch->tick($ttl, $radius, $limit);

            // Give up on searches nobody answered inside the allowed window:
            // refund the rider, pull the request off any driver still holding an
            // offer, and tell the rider no driver was found.
            $timedOut = 0;
            foreach ($dispatch->timedOutSearches() as $bookingId) {
                try {
                    if (app(RideBookingService::class)->failMatching($bookingId)) {
                        $timedOut++;
                    }
                } catch (Throwable $e) {
                    $this->warn(sprintf('[%s] booking %d timeout failed: %s', $node->name ?? $node->id, $bookingId, $e->getMessage()));
                }
            }

            $this->line(sprintf(
                '[%s] expired=%d reoffered=%d exhausted=%d timed_out=%d',
                $node->name ?? $node->id,
                $result['expired'],
                $result['reoffered'],
                $result['exhausted'],
                $timedOut
            ));
        });
    }
}
