<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

/*
|--------------------------------------------------------------------------
| Fleet realtime + booking lifecycle schedule
|--------------------------------------------------------------------------
| In production these run as long-lived --daemon workers (see the 3-process
| realtime stack). These once-a-minute scheduler entries are the SAFETY NET:
| if a daemon dies, the ledger holds and rider notifications still catch up
| within a minute. withoutOverlapping stops a slow tick from stacking.
*/

// Drain the per-shard domain-event outbox → realtime + push transports.
Schedule::command('fleet:events-relay')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

// Activate due scheduled bookings (hold fare + dispatch ~2h before pickup).
Schedule::command('fleet:activate-scheduled')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

// Expire fixed trips whose driver-assignment SLA lapsed with no driver
// (refund the hold + mark no_driver_expired). Never strands a paying rider.
Schedule::command('fleet:fixed-sla-sweep')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();
