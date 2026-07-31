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

// Expire stale offers and advance the offer wave for anything still matching.
// Without this the FIRST wave is the only one that ever runs: a ride searches
// its opening 1 km radius at creation, finds nobody, and sits in `matching`
// for ever while a driver 2.8 km away is never asked.
Schedule::command('fleet:dispatch-tick')
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

/*
|--------------------------------------------------------------------------
| Billing + integrity
|--------------------------------------------------------------------------
*/

// Close last month's accrued plan overage into per-office invoices. The normal
// path is the renewal webhook (invoice.paid); this is the backstop for offices
// whose renewal did not arrive, so nothing accrues forever uninvoiced.
Schedule::command('fleet:overage-close')
    ->monthlyOn(1, '00:30')
    ->withoutOverlapping()
    ->runInBackground();

// End trials that have run out. Without this an office stayed "trialing" for
// ever — full entitlement, no payment, no signal to anyone.
Schedule::command('fleet:subscriptions-sweep')
    ->dailyAt('02:30')
    ->withoutOverlapping()
    ->runInBackground();

// Catch offices whose payment went through but whose webhook never arrived —
// they paid and the panel still calls them unsubscribed.
Schedule::command('fleet:subscriptions-reconcile')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground();

// Ledger invariants (balanced, in-sync, conserved, non-negative) across shards.
// Nightly and quiet — it only speaks up when money stops adding up.
Schedule::command('fleet:ledger-verify')
    ->dailyAt('03:15')
    ->withoutOverlapping()
    ->runInBackground();
