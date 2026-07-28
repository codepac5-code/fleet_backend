<?php

namespace App\Http\Core\Classes\Ops;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * Liveness tracking for the background stack. Each daemon writes a heartbeat every
 * loop; the ops panel reads the last one and flags any daemon whose beat is older
 * than its expected cadence. Backed by the shared (file) cache so the daemon and
 * the web request see the same store without a migration. Global infra — NOT
 * per-country; a heartbeat is host-wide, not shard-scoped.
 */
class HeartbeatService
{
    /** daemon => stale threshold in seconds (a few multiples of its loop sleep). */
    public const DAEMONS = [
        'events-relay' => 30,
        'dispatch-tick' => 30,
        'fixed-sla-sweep' => 180,
        'queue-worker' => 120,
    ];

    private const TTL_HOURS = 24;

    public function beat(string $daemon): void
    {
        try {
            Cache::put($this->key($daemon), Carbon::now()->getTimestamp(), Carbon::now()->addHours(self::TTL_HOURS));
        } catch (Throwable $e) {
        }
    }

    /** Last-seen + liveness for every known daemon, for the ops panel. */
    public function all(): array
    {
        $now = Carbon::now()->getTimestamp();
        $out = [];

        foreach (self::DAEMONS as $name => $threshold) {
            $ts = $this->read($name);
            $ago = $ts === null ? null : max(0, $now - $ts);

            $out[] = [
                'name' => $name,
                'threshold' => $threshold,
                'seen' => $ts !== null,
                'ago' => $ago,
                'last' => $ts === null ? null : Carbon::createFromTimestamp($ts),
                'up' => $ago !== null && $ago <= $threshold,
            ];
        }

        return $out;
    }

    private function read(string $daemon): ?int
    {
        try {
            $ts = Cache::get($this->key($daemon));

            return $ts === null ? null : (int) $ts;
        } catch (Throwable $e) {
            return null;
        }
    }

    private function key(string $daemon): string
    {
        return 'fleet:heartbeat:' . $daemon;
    }
}
