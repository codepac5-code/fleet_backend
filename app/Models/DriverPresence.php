<?php

namespace App\Models;

use App\Traits\ResolvesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class DriverPresence extends Model
{
    use ResolvesTenantConnection;

    protected $table = 'driver_presence';
    protected $primaryKey = 'driver_id';
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'driver_id',
        'office_id',
        'status',
        'busy_reason',
        'lat',
        'lng',
        'geohash',
        'heartbeat_at',
        'online_since',
        'online_seconds_today',
        'online_date',
    ];

    protected $casts = [
        'driver_id' => 'integer',
        'office_id' => 'integer',
        'lat' => 'float',
        'lng' => 'float',
        'heartbeat_at' => 'datetime',
        'online_since' => 'datetime',
        'online_seconds_today' => 'integer',
        'online_date' => 'date',
    ];

    /**
     * Fold a presence status change into the online-time accumulators (called
     * on each heartbeat, before save). Time only accrues while `status` is
     * `online`; `busy`/`off`/`offline` close the open session. Rolls the daily
     * accumulator over at midnight.
     */
    public function accumulateOnline(string $newStatus): void
    {
        $now = Carbon::now();
        $today = $now->toDateString();

        // New day → reset the daily accumulator (and rebase any open session).
        if ($this->online_date === null || $this->online_date->toDateString() !== $today) {
            $this->online_seconds_today = 0;
            $this->online_date = $today;
            if ($this->online_since !== null) {
                $this->online_since = $now;
            }
        }

        if ($newStatus === 'online') {
            // Open a session if one isn't already running.
            if ($this->online_since === null) {
                $this->online_since = $now;
            }
        } else {
            // Close the running session, banking its elapsed seconds.
            if ($this->online_since !== null) {
                $this->online_seconds_today =
                    (int) $this->online_seconds_today + (int) abs($now->diffInSeconds($this->online_since));
                $this->online_since = null;
            }
        }
    }

    /**
     * Seconds online so far today, including any session still open right now.
     */
    public function onlineSecondsToday(): int
    {
        $now = Carbon::now();

        // A stale open session from a previous day counts only today's slice.
        if ($this->online_date !== null && $this->online_date->toDateString() !== $now->toDateString()) {
            return $this->online_since !== null
                ? (int) abs($now->diffInSeconds($now->copy()->startOfDay()))
                : 0;
        }

        $open = $this->online_since !== null ? (int) abs($now->diffInSeconds($this->online_since)) : 0;

        return (int) $this->online_seconds_today + $open;
    }
}
