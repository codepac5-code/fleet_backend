<?php

namespace App\Http\Core\Classes\Dispatch;

use App\Http\Core\GeoServices\ShardManager;

use App\Http\Core\Classes\Event\DomainEvent;
use App\Http\Core\Classes\Event\EventBus;
use App\Http\Core\Classes\Settings\AppSettings;
use App\Http\Core\Const\Dispatch\DispatchStatus;
use App\Http\Core\Const\Dispatch\OfferStatus;
use App\Http\Core\Const\Dispatch\PresenceStatus;
use App\Http\Core\Const\Event\Channel;
use App\Http\Core\Const\Event\EventType;
use App\Models\DispatchJob;
use App\Models\DispatchOffer;
use App\Models\Driver;
use App\Models\DriverPresence;
use App\Models\RideBooking;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class DispatchService
{
    /**
     * The event bus is NOT optional. It used to be nullable, which meant a
     * DispatchService built without one ran the whole dispatch flow — offers,
     * assignment, cancellation — emitting nothing at all, silently. Defaulting
     * to a real bus makes losing events impossible by construction.
     */
    public function __construct(private EventBus $events = new EventBus())
    {
    }

    public function heartbeat(int $driverId, ?int $officeId, string $status, ?float $lat, ?float $lng, ?string $busyReason = null): DriverPresence
    {
        $existing = DriverPresence::query()->where('driver_id', $driverId)->first();
        $changed = $existing === null || $existing->status !== $status || $existing->busy_reason !== $busyReason;

        $presence = DriverPresence::query()->updateOrCreate(
            ['driver_id' => $driverId],
            [
                'office_id' => $officeId,
                'status' => $status,
                'busy_reason' => $status === PresenceStatus::BUSY ? $busyReason : null,
                'lat' => $lat,
                'lng' => $lng,
                'heartbeat_at' => now(),
            ]
        );

        // Mirror the current availability onto the drivers row so the single
        // driver record also carries is_online / busy / busy_reason. This is a
        // secondary projection: presence itself is already recorded above, so a
        // failure here must not fail the driver's status change.
        try {
            Driver::query()->whereKey($driverId)->update([
                'is_online' => $status === PresenceStatus::ONLINE,
                'busy' => $status === PresenceStatus::BUSY,
                'busy_reason' => $status === PresenceStatus::BUSY ? $busyReason : null,
            ]);
        } catch (Throwable $e) {
            Log::warning("heartbeat: drivers mirror failed for {$driverId} — " . $e->getMessage());
        }

        $region = ShardManager::shardKey();

        if ($status !== PresenceStatus::ONLINE && $status !== PresenceStatus::BUSY) {
            // Going offline drops the cached position immediately, so the
            // dispatcher can never match a driver who just made themselves
            // unavailable.
            DriverLocationStore::forget($region, $driverId);
        } elseif ($lat !== null && $lng !== null) {
            // A heartbeat that carries coordinates seeds/refreshes the position
            // too, so presence and location never disagree (the app's 15s socket
            // fixes remain the primary, higher-frequency source).
            DriverLocationStore::put($region, $driverId, $lat, $lng);
        }

        if ($changed && $this->events !== null) {
            $channels = [Channel::driver($driverId)];

            if ($officeId !== null) {
                $channels[] = Channel::office($officeId);
            }

            $this->events->emit(new DomainEvent(
                EventType::PRESENCE_CHANGED,
                $channels,
                ['driver_id' => $driverId, 'status' => $status, 'busy_reason' => $presence->busy_reason, 'office_id' => $officeId]
            ));
        }

        return $presence;
    }

    public function createJob(int $bookingId, int $officeId, ?string $serviceClass, float $lat, float $lng): DispatchJob
    {
        return DispatchJob::query()->firstOrCreate(
            ['booking_id' => $bookingId],
            [
                'office_id' => $officeId,
                'service_class' => $serviceClass,
                'lat' => $lat,
                'lng' => $lng,
                'status' => DispatchStatus::PENDING,
                'wave' => 0,
            ]
        );
    }

    /**
     * Drivers available for a pickup, nearest first.
     *
     * Positions come from Redis ([[DriverLocationStore]]) — the gateway writes
     * them the moment the driver's app reports a fix, and they vanish when the
     * driver goes offline or the TTL lapses. Redis answers "who is near"; the
     * database is still consulted to confirm each candidate is genuinely
     * assignable (same office, status ONLINE) before a ride is offered.
     */
    public function findCandidates(int $officeId, float $lat, float $lng, float $radiusMeters, int $limit = 10, int $freshSeconds = 60, array $excludeDriverIds = []): array
    {
        $region = ShardManager::shardKey();

        // Ask Redis for everyone in range (over-fetch: some will fail the checks).
        $nearby = DriverLocationStore::search($region, $lat, $lng, $radiusMeters, max($limit * 4, $limit));

        if ($nearby === []) {
            return [];
        }

        $byId = [];
        foreach ($nearby as $hit) {
            if (! in_array($hit['driver_id'], $excludeDriverIds, true)) {
                $byId[$hit['driver_id']] = $hit['distance_m'];
            }
        }

        if ($byId === []) {
            return [];
        }

        // Status gate — only drivers this office can dispatch and who are ONLINE.
        $assignable = DriverPresence::query()
            ->whereIn('driver_id', array_keys($byId))
            ->where('office_id', $officeId)
            ->where('status', PresenceStatus::ONLINE)
            ->pluck('driver_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $candidates = [];
        foreach ($assignable as $driverId) {
            $candidates[] = ['driver_id' => $driverId, 'distance_m' => (float) $byId[$driverId]];
        }

        usort($candidates, fn ($a, $b) => $a['distance_m'] <=> $b['distance_m']);

        return array_slice($candidates, 0, $limit);
    }

    /**
     * Search radius for the next offer wave. Starts at `dispatch_radius_m` (the
     * tight first ring, e.g. 1 km) and widens by `dispatch_radius_step_m` for
     * every wave that went unanswered, capped at `dispatch_radius_max_m`. So a
     * ride is offered to the closest drivers first and only reaches further ones
     * when nobody nearby takes it.
     */
    public function waveRadius(int $currentWave): float
    {
        $base = (float) AppSettings::int('dispatch_radius_m', 1000);
        $step = (float) AppSettings::int('dispatch_radius_step_m', 1000);
        $max = (float) AppSettings::int('dispatch_radius_max_m', 5000);

        return min($base + max(0, $currentWave) * $step, $max);
    }

    public function offerWave(int $bookingId, ?int $ttlSeconds = null, ?float $radiusMeters = null, int $limit = 5, int $freshSeconds = 60): array
    {
        $ttlSeconds = $ttlSeconds ?? AppSettings::int('dispatch_offer_ttl_s', 20);

        $job = DispatchJob::query()->where('booking_id', $bookingId)->first();

        if (!$job) {
            throw new RuntimeException('no dispatch job for booking ' . $bookingId);
        }

        if (in_array($job->status, [DispatchStatus::ASSIGNED, DispatchStatus::CANCELLED], true)) {
            throw new RuntimeException('job ' . $bookingId . ' is already ' . $job->status);
        }

        // Progressive search radius: start tight (nearest drivers first) and widen
        // with each unanswered wave, so a ride is never left unserved.
        $radiusMeters = $radiusMeters ?? $this->waveRadius((int) $job->wave);

        // Skip drivers who explicitly said no, already won it, or are still
        // holding an open offer. A driver whose offer merely TIMED OUT stays
        // eligible — they may have been mid-fare a minute ago, and in a thin
        // market excluding them forever exhausts the candidate pool.
        $alreadyOffered = DispatchOffer::query()
            ->where('booking_id', $bookingId)
            ->whereIn('status', [OfferStatus::REJECTED, OfferStatus::ACCEPTED, OfferStatus::OFFERED])
            ->pluck('driver_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        // Over-fetch so the priority split below still has a full wave to give.
        $candidates = $this->findCandidates(
            (int) $job->office_id,
            (float) $job->lat,
            (float) $job->lng,
            $radiusMeters,
            max($limit * 3, $limit),
            $freshSeconds,
            $alreadyOffered
        );

        if (empty($candidates)) {
            return [];
        }

        // Reach drivers who have not seen this ride yet BEFORE going back to
        // anyone whose earlier offer timed out. Both groups stay nearest-first,
        // but a fresh pair of eyes beats re-pestering someone who already let it
        // lapse — even if that person is closer.
        $previouslyLapsed = DispatchOffer::query()
            ->where('booking_id', $bookingId)
            ->where('status', OfferStatus::EXPIRED)
            ->pluck('driver_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($previouslyLapsed !== []) {
            $fresh = [];
            $lapsed = [];

            foreach ($candidates as $candidate) {
                if (in_array($candidate['driver_id'], $previouslyLapsed, true)) {
                    $lapsed[] = $candidate;
                } else {
                    $fresh[] = $candidate;
                }
            }

            $candidates = array_merge($fresh, $lapsed);
        }

        $candidates = array_slice($candidates, 0, $limit);

        $wave = (int) $job->wave + 1;
        $expiresAt = now()->addSeconds($ttlSeconds);
        $created = [];

        foreach ($candidates as $candidate) {
            $created[] = DispatchOffer::query()->create([
                'booking_id' => $bookingId,
                'driver_id' => $candidate['driver_id'],
                'wave' => $wave,
                'status' => OfferStatus::OFFERED,
                'distance_m' => $candidate['distance_m'],
                'expires_at' => $expiresAt,
            ]);
        }

        $job->wave = $wave;
        $job->status = DispatchStatus::OFFERED;
        $job->save();

        if ($this->events) {
            foreach ($created as $offer) {
                // The offered driver hears it (unchanged) AND the owning office
                // now sees the wave in progress live — additive, apps unaffected.
                $offerChannels = [Channel::driver((int) $offer->driver_id)];

                if ((int) $job->office_id > 0) {
                    $offerChannels[] = Channel::office((int) $job->office_id);
                }

                // The live-ops board watches the matching wave itself; without
                // the fleet room an admin sees a booking sit in `matching` with
                // no way to tell whether anyone is being asked.
                $offerChannels[] = Channel::admin();

                $this->events->emit(new DomainEvent(
                    EventType::DISPATCH_OFFER_CREATED,
                    $offerChannels,
                    [
                        'booking_id' => $bookingId,
                        'office_id' => (int) $job->office_id,
                        'distance_m' => (float) $offer->distance_m,
                        'expires_at' => (string) $expiresAt,
                    ]
                ));
            }
        }

        return $created;
    }

    public function accept(int $bookingId, int $driverId): bool
    {
        $offer = DispatchOffer::query()
            ->where('booking_id', $bookingId)
            ->where('driver_id', $driverId)
            ->where('status', OfferStatus::OFFERED)
            ->first();

        if (!$offer) {
            return false;
        }

        // A driver whose unpaid dues have hit the debt ceiling cannot take new
        // rides until they top up and settle — otherwise the debt grows unbounded.
        $currency = (string) (RideBooking::query()->where('id', $bookingId)->value('currency_code') ?: ShardManager::currency());
        if (app(\App\Http\Core\Classes\Ledger\DriverDebtPolicy::class)->isBlocked($driverId, $currency)) {
            throw \App\Http\Core\Exceptions\DomainException::make('debt_ceiling_exceeded', 403);
        }

        if ($offer->expires_at && Carbon::parse($offer->expires_at)->isPast()) {
            $offer->status = OfferStatus::EXPIRED;
            $offer->save();

            return false;
        }

        $connection = (new DispatchJob)->getConnectionName();

        return DB::connection($connection)->transaction(function () use ($bookingId, $driverId) {
            $claimed = DispatchJob::query()
                ->where('booking_id', $bookingId)
                ->whereNull('assigned_driver_id')
                ->whereIn('status', [DispatchStatus::PENDING, DispatchStatus::OFFERED])
                ->update([
                    'assigned_driver_id' => $driverId,
                    'status' => DispatchStatus::ASSIGNED,
                    'assigned_at' => now(),
                ]);

            if ($claimed !== 1) {
                return false;
            }

            DispatchOffer::query()
                ->where('booking_id', $bookingId)
                ->where('driver_id', $driverId)
                ->update(['status' => OfferStatus::ACCEPTED]);

            $this->expireLosingOffers($bookingId, $driverId);

            DriverPresence::query()
                ->where('driver_id', $driverId)
                ->update(['status' => PresenceStatus::BUSY]);

            RideBooking::query()
                ->where('id', $bookingId)
                ->update(['driver_id' => $driverId, 'assigned_at' => now()]);

            if ($this->events) {
                $job = DispatchJob::query()->where('booking_id', $bookingId)->first();
                $booking = RideBooking::query()->where('id', $bookingId)->first();

                $channels = [Channel::booking($bookingId), Channel::driver($driverId), Channel::office((int) $job->office_id)];

                if ($booking !== null) {
                    $channels[] = Channel::user((int) $booking->user_id);
                }

                $this->events->emit(new DomainEvent(
                    EventType::DISPATCH_RIDE_ASSIGNED,
                    $channels,
                    $this->assignedPayload($bookingId, $driverId, (int) $job->office_id, $job->service_class)
                ));
            }

            return true;
        });
    }

    public function assignDriver(int $bookingId, int $driverId): bool
    {
        $connection = (new DispatchJob)->getConnectionName();

        return DB::connection($connection)->transaction(function () use ($bookingId, $driverId) {
            $claimed = DispatchJob::query()
                ->where('booking_id', $bookingId)
                ->whereNull('assigned_driver_id')
                ->whereIn('status', [DispatchStatus::PENDING, DispatchStatus::OFFERED])
                ->update([
                    'assigned_driver_id' => $driverId,
                    'status' => DispatchStatus::ASSIGNED,
                    'assigned_at' => now(),
                ]);

            if ($claimed !== 1) {
                return false;
            }

            // Force-assign: everyone still holding an offer loses it — tell them.
            $this->expireLosingOffers($bookingId, $driverId);

            DriverPresence::query()
                ->where('driver_id', $driverId)
                ->update(['status' => PresenceStatus::BUSY]);

            RideBooking::query()
                ->where('id', $bookingId)
                ->update(['driver_id' => $driverId, 'assigned_at' => now()]);

            if ($this->events) {
                $job = DispatchJob::query()->where('booking_id', $bookingId)->first();
                $booking = RideBooking::query()->where('id', $bookingId)->first();

                $channels = [Channel::booking($bookingId), Channel::driver($driverId), Channel::office((int) $job->office_id)];

                if ($booking !== null) {
                    $channels[] = Channel::user((int) $booking->user_id);
                }

                $this->events->emit(new DomainEvent(
                    EventType::DISPATCH_RIDE_ASSIGNED,
                    $channels,
                    $this->assignedPayload($bookingId, $driverId, (int) $job->office_id, $job->service_class)
                ));
            }

            return true;
        });
    }

    public function forceAssign(int $bookingId, int $driverId, int $officeId, ?string $serviceClass, float $lat, float $lng): bool
    {
        $connection = (new DispatchJob)->getConnectionName();

        return DB::connection($connection)->transaction(function () use ($bookingId, $driverId, $officeId, $serviceClass, $lat, $lng) {
            $job = DispatchJob::query()->firstOrCreate(
                ['booking_id' => $bookingId],
                [
                    'office_id' => $officeId,
                    'service_class' => $serviceClass,
                    'lat' => $lat,
                    'lng' => $lng,
                    'status' => DispatchStatus::PENDING,
                    'wave' => 0,
                ]
            );

            $job->assigned_driver_id = $driverId;
            $job->status = DispatchStatus::ASSIGNED;
            $job->assigned_at = now();
            $job->save();

            $this->expireLosingOffers($bookingId, $driverId);

            DriverPresence::query()
                ->where('driver_id', $driverId)
                ->update(['status' => PresenceStatus::BUSY]);

            RideBooking::query()
                ->where('id', $bookingId)
                ->update(['driver_id' => $driverId, 'assigned_at' => now()]);

            if ($this->events) {
                $booking = RideBooking::query()->where('id', $bookingId)->first();

                $channels = [Channel::booking($bookingId), Channel::driver($driverId), Channel::office($officeId)];

                if ($booking !== null) {
                    $channels[] = Channel::user((int) $booking->user_id);
                }

                $this->events->emit(new DomainEvent(
                    EventType::DISPATCH_RIDE_ASSIGNED,
                    $channels,
                    $this->assignedPayload($bookingId, $driverId, $officeId, $serviceClass)
                ));
            }

            return true;
        });
    }

    public function reject(int $bookingId, int $driverId): void
    {
        $updated = DispatchOffer::query()
            ->where('booking_id', $bookingId)
            ->where('driver_id', $driverId)
            ->where('status', OfferStatus::OFFERED)
            ->update(['status' => OfferStatus::REJECTED]);

        if ($updated === 0) {
            return;
        }

        // Once the last holder says no there is nothing left to wait for — widen
        // and re-offer immediately instead of burning the rest of the offer TTL
        // on dead air.
        $stillHeld = DispatchOffer::query()
            ->where('booking_id', $bookingId)
            ->where('status', OfferStatus::OFFERED)
            ->exists();

        if ($stillHeld) {
            return;
        }

        try {
            $this->offerWave($bookingId);
        } catch (RuntimeException $e) {
            // Job already assigned/cancelled between the reject and here.
        }
    }

    public function expireStaleOffers(): int
    {
        $expiring = DispatchOffer::query()
            ->where('status', OfferStatus::OFFERED)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->get(['id', 'booking_id', 'driver_id']);

        if ($expiring->isEmpty()) {
            return 0;
        }

        DispatchOffer::query()->whereIn('id', $expiring->pluck('id'))->update(['status' => OfferStatus::EXPIRED]);

        if ($this->events !== null) {
            foreach ($expiring as $offer) {
                $this->events->emit(new DomainEvent(
                    EventType::DISPATCH_OFFER_EXPIRED,
                    [Channel::driver((int) $offer->driver_id), Channel::booking((int) $offer->booking_id)],
                    ['booking_id' => (int) $offer->booking_id, 'driver_id' => (int) $offer->driver_id]
                ));
            }
        }

        return $expiring->count();
    }

    /** @param float|null $radiusMeters null → progressive per-wave radius (see waveRadius). */
    public function tick(int $ttlSeconds = 20, ?float $radiusMeters = null, int $limit = 5, int $freshSeconds = 60): array
    {
        $expired = $this->expireStaleOffers();
        $reoffered = 0;
        $exhausted = 0;

        $jobs = DispatchJob::query()
            ->whereIn('status', [DispatchStatus::PENDING, DispatchStatus::OFFERED])
            ->whereNull('assigned_driver_id')
            ->get();

        foreach ($jobs as $job) {
            $hasActiveOffer = DispatchOffer::query()
                ->where('booking_id', $job->booking_id)
                ->where('status', OfferStatus::OFFERED)
                ->exists();

            if ($hasActiveOffer) {
                continue;
            }

            $created = $this->offerWave((int) $job->booking_id, $ttlSeconds, $radiusMeters, $limit, $freshSeconds);

            if ($created === []) {
                $exhausted++;
            } else {
                $reoffered++;
            }
        }

        return ['expired' => $expired, 'reoffered' => $reoffered, 'exhausted' => $exhausted];
    }

    /**
     * Bookings whose search has run past the allowed window with nobody
     * assigned. The caller ends them properly (refund + notify) — a search that
     * never resolves is worse for the rider than an honest "no driver found".
     *
     * @return array<int, int> booking ids
     */
    public function timedOutSearches(?int $timeoutSeconds = null): array
    {
        $timeout = $timeoutSeconds ?? AppSettings::int('dispatch_search_timeout_s', 180);

        return DispatchJob::query()
            ->whereIn('status', [DispatchStatus::PENDING, DispatchStatus::OFFERED])
            ->whereNull('assigned_driver_id')
            ->where('created_at', '<', now()->subSeconds($timeout))
            ->pluck('booking_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function cancelJob(int $bookingId, string $reason = 'cancelled', string $cancelledBy = 'rider'): void
    {
        DispatchJob::query()
            ->where('booking_id', $bookingId)
            ->whereIn('status', [DispatchStatus::PENDING, DispatchStatus::OFFERED])
            ->update(['status' => DispatchStatus::CANCELLED]);

        $this->withdrawOffers($bookingId, $reason, $cancelledBy);
    }

    /**
     * Expire every outstanding offer AND tell those drivers the request is gone,
     * so the incoming-ride sheet disappears from their screen immediately
     * instead of running its countdown out on a ride nobody can take.
     */
    public function withdrawOffers(int $bookingId, string $reason = 'cancelled', string $cancelledBy = 'rider'): int
    {
        // Capture the holders BEFORE expiring them — afterwards they are
        // indistinguishable from offers that simply timed out.
        $holders = DispatchOffer::query()
            ->where('booking_id', $bookingId)
            ->where('status', OfferStatus::OFFERED)
            ->pluck('driver_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($holders === []) {
            return 0;
        }

        DispatchOffer::query()
            ->where('booking_id', $bookingId)
            ->where('status', OfferStatus::OFFERED)
            ->update(['status' => OfferStatus::EXPIRED]);

        if ($this->events !== null) {
            $this->events->emit(new DomainEvent(
                EventType::DISPATCH_JOB_CANCELLED,
                array_map(fn (int $id) => Channel::driver($id), $holders),
                ['booking_id' => $bookingId, 'cancelled_by' => $cancelledBy, 'reason' => $reason]
            ));
        }

        return count($holders);
    }

    public function releaseAssignedDriver(int $driverId): void
    {
        DriverPresence::query()
            ->where('driver_id', $driverId)
            ->update([
                'status' => PresenceStatus::ONLINE,
                'busy_reason' => null,
            ]);
    }

    public function cancelForBooking(int $bookingId, ?int $driverId, string $reason = 'cancelled', string $cancelledBy = 'office'): void
    {
        // Tell any drivers holding an offer the ride is gone, then cancel the job
        // in ANY still-active state (a scheduled ride the office pre-assigned sits
        // in ASSIGNED, which `cancelJob` deliberately leaves alone for the rider).
        $this->withdrawOffers($bookingId, $reason, $cancelledBy);

        DispatchJob::query()
            ->where('booking_id', $bookingId)
            ->whereIn('status', [DispatchStatus::PENDING, DispatchStatus::OFFERED, DispatchStatus::ASSIGNED])
            ->update(['status' => DispatchStatus::CANCELLED]);

        if ($driverId !== null) {
            $this->releaseAssignedDriver($driverId);
        }
    }

    /**
     * Payload for `dispatch.ride_assigned`.
     *
     * The rider app renders its driver card straight from this event — name,
     * rating, vehicle and ETA. Emitting only ids left it showing placeholders
     * ("Driver", "White Vehicle · 000 000"), so the real identity is included
     * here. Extra keys are additive: the driver app only reads booking_id and
     * driver_id.
     */
    private function assignedPayload(int $bookingId, int $driverId, int $officeId, ?string $serviceClass = null): array
    {
        $payload = [
            'booking_id' => $bookingId,
            'driver_id' => $driverId,
            'office_id' => $officeId,
        ];

        // Decoration only — never let a lookup failure block the assignment
        // itself. Worst case the rider gets ids and falls back to placeholders.
        try {
            $driver = Driver::query()->find($driverId);

            if ($driver !== null) {
                $payload['driver'] = [
                    'id' => (int) $driver->id,
                    'name' => trim(((string) $driver->firstName) . ' ' . ((string) $driver->lastName)),
                    'rating' => (float) ($driver->rating ?? 0),
                ];

                $vehicle = $driver->vehicleId !== null ? $driver->vehicle : null;

                if ($vehicle !== null) {
                    $payload['vehicle'] = [
                        'model' => trim(((string) $vehicle->vehicleBrand) . ' ' . ((string) $vehicle->model)),
                        'plate' => (string) $vehicle->plate,
                        // The rider app spells this key `colour`.
                        'colour' => (string) $vehicle->color,
                        'class_label' => $serviceClass !== null ? strtoupper($serviceClass) : null,
                    ];
                }
            }

            // ETA straight from how far the winning driver actually was.
            $distance = DispatchOffer::query()
                ->where('booking_id', $bookingId)
                ->where('driver_id', $driverId)
                ->orderByDesc('id')
                ->value('distance_m');

            if ($distance !== null) {
                $payload['distance_m'] = (int) $distance;
                // ~400 m per minute is a realistic city crawl.
                $payload['eta_minutes'] = max(1, (int) ceil(((float) $distance) / 400));
            }
        } catch (Throwable $e) {
            Log::warning("assignedPayload: could not enrich booking {$bookingId} — " . $e->getMessage());
        }

        return $payload;
    }

    /**
     * Close the losing drivers' sheets the instant somebody wins the ride, so
     * they stop staring at an offer that would now fail with a 409.
     */
    private function expireLosingOffers(int $bookingId, int $winnerDriverId): void
    {
        $losers = DispatchOffer::query()
            ->where('booking_id', $bookingId)
            ->where('driver_id', '!=', $winnerDriverId)
            ->where('status', OfferStatus::OFFERED)
            ->pluck('driver_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($losers === []) {
            return;
        }

        DispatchOffer::query()
            ->where('booking_id', $bookingId)
            ->where('driver_id', '!=', $winnerDriverId)
            ->where('status', OfferStatus::OFFERED)
            ->update(['status' => OfferStatus::EXPIRED]);

        if ($this->events !== null) {
            $this->events->emit(new DomainEvent(
                EventType::DISPATCH_OFFER_EXPIRED,
                array_map(fn (int $id) => Channel::driver($id), $losers),
                ['booking_id' => $bookingId, 'reason' => 'taken_by_another_driver']
            ));
        }
    }
}
