<?php

namespace App\Console\Commands;

use App\Http\Core\Classes\Ops\HeartbeatService;
use App\Http\Core\Classes\Ride\FixedTripService;
use App\Http\Core\GeoServices\ShardRunner;
use Illuminate\Console\Command;

/**
 * Sweep office-mediated fixed trips whose driver-assignment SLA has lapsed with
 * no driver assigned: refund the held fare and mark `no_driver_expired`, per
 * shard. Never strands a paying rider — the hold is always released.
 */
class FixedTripSlaSweep extends Command
{
    protected $signature = 'fleet:fixed-sla-sweep {--daemon} {--sleep=60}';

    protected $description = 'Expire overdue fixed-trip driver assignments (refund + no_driver_expired), per shard';

    public function handle(): int
    {
        if ($this->option('daemon')) {
            $sleep = max(1, (int) $this->option('sleep'));

            while (true) {
                app(HeartbeatService::class)->beat('fixed-sla-sweep');
                $this->sweepAllShards();
                sleep($sleep);
            }
        }

        $this->sweepAllShards();

        return self::SUCCESS;
    }

    private function sweepAllShards(): void
    {
        ShardRunner::eachCountry(function ($node) {
            $expired = app(FixedTripService::class)->expireOverdueAssignments();

            $this->line(sprintf('[%s] expired=%d', $node->name ?? $node->id, $expired));
        });
    }
}
