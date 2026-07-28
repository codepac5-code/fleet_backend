<?php

namespace App\Console\Commands;

use App\Http\Core\Classes\Ride\ScheduledActivationService;
use App\Http\Core\GeoServices\ShardRunner;
use Illuminate\Console\Command;

class ActivateScheduled extends Command
{
    protected $signature = 'fleet:activate-scheduled {--lead=7200} {--limit=100} {--daemon} {--sleep=60}';

    protected $description = 'Activate due scheduled bookings (hold fare + dispatch ~lead seconds before pickup), per shard';

    public function handle(): int
    {
        $lead = (int) $this->option('lead');
        $limit = (int) $this->option('limit');

        if ($this->option('daemon')) {
            $sleep = max(1, (int) $this->option('sleep'));

            while (true) {
                $this->activateAllShards($lead, $limit);
                sleep($sleep);
            }
        }

        $this->activateAllShards($lead, $limit);

        return self::SUCCESS;
    }

    private function activateAllShards(int $lead, int $limit): void
    {
        ShardRunner::eachCountry(function ($node) use ($lead, $limit) {
            $activated = app(ScheduledActivationService::class)->activateDue($lead, $limit);

            $this->line(sprintf('[%s] activated=%d', $node->name ?? $node->id, $activated));
        });
    }
}
