<?php

namespace App\Console\Commands;

use App\Http\Core\Classes\Event\OutboxRelay;
use App\Http\Core\Classes\Ops\HeartbeatService;
use App\Http\Core\GeoServices\ShardRunner;
use Illuminate\Console\Command;

class RelayEventOutbox extends Command
{
    protected $signature = 'fleet:events-relay {--limit=200} {--daemon} {--sleep=2}';

    protected $description = 'Publish pending domain events from the per-shard outbox to realtime and notification transports';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');

        if ($this->option('daemon')) {
            $sleep = max(1, (int) $this->option('sleep'));

            while (true) {
                app(HeartbeatService::class)->beat('events-relay');
                $this->drainAllShards($limit);
                sleep($sleep);
            }
        }

        $this->drainAllShards($limit);

        return self::SUCCESS;
    }

    private function drainAllShards(int $limit): void
    {
        ShardRunner::eachCountry(function ($node) use ($limit) {
            $result = app(OutboxRelay::class)->publishPending($limit);

            $this->line(sprintf(
                '[%s] published=%d retried=%d failed=%d',
                $node->name ?? $node->id,
                $result['published'],
                $result['retried'],
                $result['failed']
            ));
        });
    }
}
