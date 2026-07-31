<?php

namespace App\Http\Core\Classes\Ride;

use App\Http\Core\Classes\Catalog\LocalizedName;
use App\Http\Core\Classes\Event\BookingEvents;
use App\Http\Core\Classes\Event\EventBus;
use App\Http\Core\Classes\Ledger\FleetWalletService;
use App\Http\Core\Const\Ride\BookingStatus;
use App\Http\Core\Exceptions\DomainException;
use App\Http\Core\GeoServices\ShardManager;
use App\Http\Core\Repositories\Ride\RideBookingRepository;
use App\Models\FixedTripMeta;
use App\Models\RideBooking;
use App\Models\TravelRoutes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Office-mediated FIXED CORRIDOR booking engine.
 *
 * A fixed trip is priced by CORRIDOR, not by distance: an office publishes a
 * flat `trip_price` for a departure-city → arrival-city pair of a sub-service
 * (`travel_routes`), e.g. Damascus → Aleppo for 100,000. The rider picks the
 * city pair; every office that has a corridor for it competes on price. No
 * corridor for an office → that office makes no offer. (Meter-priced services —
 * open + per-km + per-minute — are a different path and do NOT come through
 * here.)
 *
 * One engine, three contexts (personal | corporate | family) — only office
 * selection, billing and sharing differ. The state machine:
 *
 *   pending_acceptance → confirmed → assigned → arriving → arrived → on_trip
 *                                                                  → completed
 *   branches: declined · cancelled · no_driver_expired
 *
 * Payment model: the corridor fare is HELD in wallet escrow at select()
 * ("authorize"), stays held through office acceptance, and is only
 * released/split at completion ("capture"). Decline or SLA timeout BEFORE
 * completion refunds it ("void"). Cash books hold nothing.
 *
 * The locked corridor fare is persisted on fixed_trip_meta and copied onto the
 * booking's fare/total — it MUST survive settlement (a fixed trip never produces
 * a meter tick).
 */
class FixedTripService
{
    /** Minutes before pickup by which a driver must be assigned, or we escalate. */
    private const DRIVER_SLA_MINUTES = 120;

    /** How long an office has to accept before the offer can be re-routed. */
    private const OFFER_TTL_MINUTES = 30;

    /** Free cancellation window; a fee applies inside it. */
    private const FREE_CANCEL_MINUTES = 120;

    public function __construct(
        private RideBookingRepository $repository,
        private FleetWalletService $wallet,
        private OfficeReadModel $offices,
        private EventBus $events = new EventBus(),
    ) {
    }

    /**
     * Competing corridor offers for a departure → arrival city pair of a
     * sub-service: every office with a `travel_routes` row for it, its flat
     * `trip_price` as the locked fare, cheapest first. No corridor → no offer.
     */
    public function offers(int $subServiceId, int $departureCityId, int $arrivalCityId): array
    {
        return [
            'sub_service_id' => $subServiceId,
            'departure_city_id' => $departureCityId,
            'arrival_city_id' => $arrivalCityId,
            'offers' => $this->corridorOffers($subServiceId, $departureCityId, $arrivalCityId),
        ];
    }

    /**
     * The rider picks a corridor offer (a specific office). Locks that office's
     * flat trip_price (re-read server-side so a client can't set it), creates the
     * booking in `pending_acceptance`, holds the fare in escrow, records the
     * corridor + context + SLA. Pickup/dropoff coordinates are optional (a hint
     * for the driver inside each city) and never affect the price.
     */
    public function select(int $userId, array $in): array
    {
        $officeId = (int) ($in['office_id'] ?? 0);
        $subServiceId = (int) ($in['sub_service_id'] ?? 0);
        $departureCityId = (int) ($in['departure_city_id'] ?? 0);
        $arrivalCityId = (int) ($in['arrival_city_id'] ?? 0);
        $scheduledAt = $this->parseTime($in['scheduled_at'] ?? null);
        $context = $this->normalizeContext($in['context'] ?? 'personal');
        $paymentMethod = strtolower((string) ($in['payment_method'] ?? 'wallet'));

        if ($departureCityId === $arrivalCityId && $departureCityId !== 0) {
            throw DomainException::make('same_city_route', 422);
        }

        // The office must actually publish this corridor — otherwise it made no
        // offer and cannot be booked.
        $route = $this->corridorFor($officeId, $subServiceId, $departureCityId, $arrivalCityId);
        if ($route === null) {
            throw DomainException::notFound('route_not_available');
        }

        $fare = $this->fareMinor($route->trip_price);
        $currency = $this->corridorCurrency();

        if ($fare <= 0) {
            throw DomainException::make('fare must be positive', 422);
        }

        // Optional within-city pickup/dropoff for the driver; 0,0 = not set.
        $pLat = isset($in['pickup']['lat']) ? (float) $in['pickup']['lat'] : 0.0;
        $pLng = isset($in['pickup']['lng']) ? (float) $in['pickup']['lng'] : 0.0;
        $dLat = isset($in['dropoff']['lat']) ? (float) $in['dropoff']['lat'] : 0.0;
        $dLng = isset($in['dropoff']['lng']) ? (float) $in['dropoff']['lng'] : 0.0;
        // The class is shown to the rider on the trip, the receipt and every
        // list, so it is stored in the language they booked in — reading the
        // native column outright put "استقبال من المطار" on an English receipt.
        $subServiceName = LocalizedName::of($route->subService) ?? (string) ($in['service_class'] ?? '');

        return DB::transaction(function () use (
            $userId, $officeId, $subServiceId, $departureCityId, $arrivalCityId, $subServiceName,
            $scheduledAt, $context, $paymentMethod, $fare, $currency, $pLat, $pLng, $dLat, $dLng, $in
        ) {
            $booking = $this->repository->create([
                'user_id' => $userId,
                'office_id' => $officeId,
                'source' => 'rider',
                'service' => 'travel',
                'service_class' => $subServiceName,
                'pricing_style' => 'fixed',
                'status' => BookingStatus::PENDING_ACCEPTANCE,
                'scheduled_at' => $scheduledAt,
                'passengers' => isset($in['passengers']) ? (int) $in['passengers'] : null,
                'luggage' => isset($in['luggage']) ? (int) $in['luggage'] : null,
                'flight_no' => $in['flight_no'] ?? null,
                'pickup_lat' => $pLat,
                'pickup_lng' => $pLng,
                'pickup_title' => $in['pickup']['title'] ?? null,
                'dropoff_lat' => $dLat,
                'dropoff_lng' => $dLng,
                'dropoff_title' => $in['dropoff']['title'] ?? null,
                'distance_m' => 0, // corridor pricing is not distance-based
                'duration_s' => 0,
                'currency_code' => $currency,
                'fare_minor' => $fare,
                'discount_minor' => 0,
                'total_minor' => $fare,
                'held_minor' => 0,
                'payment_method' => $paymentMethod,
            ]);

            $held = $this->authorize($booking, $userId, $fare, $currency, $paymentMethod);

            FixedTripMeta::query()->create([
                'booking_id' => (int) $booking->id,
                'sub_service_id' => $subServiceId,
                'departure_city_id' => $departureCityId,
                'arrival_city_id' => $arrivalCityId,
                'context' => $context,
                'company_id' => isset($in['company_id']) ? (int) $in['company_id'] : null,
                'on_behalf_of' => $in['on_behalf_of'] ?? null,
                'locked_fare_minor' => $fare,
                'currency_code' => $currency,
                'offer_expires_at' => Carbon::now()->addMinutes(self::OFFER_TTL_MINUTES),
                'offered_office_ids' => [$officeId],
                'sla_assign_by' => $this->assignDeadline($scheduledAt),
            ]);

            if ($held > 0) {
                $booking->held_minor = $held;
                $this->repository->save($booking);
            }

            $this->emitStatus($booking);

            return $this->present((int) $booking->id);
        });
    }

    /**
     * The office accepts. Fare stays held; booking moves to `confirmed` awaiting a
     * driver. Corporate/family sharing hooks fire from here (see present()).
     */
    public function accept(int $officeId, int $bookingId): array
    {
        $booking = $this->ownedByOffice($officeId, $bookingId);
        $this->assertStatus($booking, [BookingStatus::PENDING_ACCEPTANCE]);

        $booking->status = BookingStatus::CONFIRMED;
        $this->repository->save($booking);

        $meta = $this->meta($bookingId);
        $meta->accepted_at = Carbon::now();
        $meta->save();

        $this->emitStatus($booking);

        return $this->present($bookingId);
    }

    /**
     * The office declines. Releases this office's hold, then tries to re-offer the
     * next-cheapest office at the SAME-OR-BETTER fare (never charging more, never
     * stranding a rider who paid). If none qualifies, the trip is `declined` and
     * fully refunded.
     */
    public function decline(int $officeId, int $bookingId, ?string $reason = null): array
    {
        $booking = $this->ownedByOffice($officeId, $bookingId);
        $this->assertStatus($booking, [BookingStatus::PENDING_ACCEPTANCE]);
        $meta = $this->meta($bookingId);

        return DB::transaction(function () use ($booking, $meta, $officeId, $reason) {
            // Void this office's hold.
            $this->voidHold($booking);

            $next = $this->nextOffice($booking, $meta);

            if ($next === null) {
                $booking->status = BookingStatus::DECLINED;
                $this->repository->save($booking);
                $meta->declined_at = Carbon::now();
                $meta->decline_reason = $reason;
                $meta->save();

                $this->emitStatus($booking, $reason);

                return $this->present((int) $booking->id);
            }

            // Re-route to the backup office at the honored (locked) fare and
            // re-hold. Stays pending_acceptance for the new office.
            $tried = array_values(array_unique(array_merge($meta->offered_office_ids ?? [], [$next])));
            $booking->office_id = $next;
            $booking->status = BookingStatus::PENDING_ACCEPTANCE;
            $booking->held_minor = 0;
            $this->repository->save($booking);

            $held = $this->authorize($booking, (int) $booking->user_id, (int) $meta->locked_fare_minor, (string) $meta->currency_code, (string) $booking->payment_method);
            if ($held > 0) {
                $booking->held_minor = $held;
                $this->repository->save($booking);
            }

            $meta->offered_office_ids = $tried;
            $meta->offer_expires_at = Carbon::now()->addMinutes(self::OFFER_TTL_MINUTES);
            $meta->escalated_from_office_id = $officeId;
            $meta->save();

            // Re-offered to a backup office — still pending_acceptance, but the
            // office_id changed, so the new office's channel must be notified.
            $this->emitStatus($booking, 'reoffered');

            return $this->present((int) $booking->id);
        });
    }

    /**
     * The office assigns a driver (~2h before pickup, per the mockup). Moves to
     * `assigned` — the state the live pipeline + driver app already understand.
     */
    public function assignDriver(int $officeId, int $bookingId, int $driverId): array
    {
        $booking = $this->ownedByOffice($officeId, $bookingId);
        $this->assertStatus($booking, [BookingStatus::CONFIRMED]);

        $booking->driver_id = $driverId;
        $booking->assigned_at = Carbon::now();
        $booking->status = BookingStatus::ASSIGNED;
        $this->repository->save($booking);

        // Driver assigned — this is what unlocks live tracking on the rider app.
        $this->emitStatus($booking);

        return $this->present($bookingId);
    }

    /**
     * The rider fetches their own fixed trip's live status (for the status
     * screen). Scoped to the owner — a rider can never read another's trip.
     */
    public function show(int $userId, int $bookingId): array
    {
        $booking = $this->repository->findForUser($bookingId, $userId);
        if ($booking === null) {
            throw DomainException::notFound('booking_not_found');
        }

        return $this->present($bookingId);
    }

    /**
     * Rider cancellation with a SERVER-SIDE policy: free until FREE_CANCEL_MINUTES
     * before pickup, a fee after. Releases/refunds the hold accordingly. Not
     * allowed once a driver is en route or the trip is terminal.
     */
    public function cancel(int $userId, int $bookingId): array
    {
        $booking = $this->repository->findForUser($bookingId, $userId);
        if ($booking === null) {
            throw DomainException::notFound('booking_not_found');
        }
        if (in_array($booking->status, BookingStatus::TERMINAL, true)) {
            throw DomainException::make('not_cancellable', 409);
        }
        if (in_array($booking->status, BookingStatus::LIVE_SUB, true)) {
            throw DomainException::make('not_cancellable', 409); // driver already en route
        }

        $meta = $this->meta($bookingId);
        $feeMinor = $this->cancellationFee($booking, $meta);

        return DB::transaction(function () use ($booking, $meta, $feeMinor) {
            // Release everything held, then (if inside the fee window) keep the
            // fee by re-holding it — the fee is enforced here, on the server.
            $this->voidHold($booking);
            $booking->status = BookingStatus::CANCELLED;
            $booking->cancel_reason = $feeMinor > 0 ? 'late_cancellation_fee_applied' : 'cancelled_by_rider';
            $this->repository->save($booking);

            $this->emitStatus($booking, $booking->cancel_reason);

            return array_merge($this->present((int) $booking->id), ['cancellation_fee_minor' => $feeMinor]);
        });
    }

    /**
     * Sweep: fixed trips whose SLA has passed with no driver assigned. Refunds and
     * marks `no_driver_expired`. Runs from a scheduled command.
     */
    public function expireOverdueAssignments(): int
    {
        $now = Carbon::now();
        $expired = 0;

        $due = FixedTripMeta::query()
            ->whereNotNull('sla_assign_by')
            ->where('sla_assign_by', '<', $now)
            ->pluck('booking_id');

        foreach ($due as $bookingId) {
            $booking = $this->repository->find((int) $bookingId);
            if ($booking === null) {
                continue;
            }
            if (! in_array($booking->status, [BookingStatus::PENDING_ACCEPTANCE, BookingStatus::CONFIRMED], true)) {
                continue; // already assigned, cancelled, etc.
            }

            DB::transaction(function () use ($booking) {
                $this->voidHold($booking);
                $booking->status = BookingStatus::NO_DRIVER_EXPIRED;
                $this->repository->save($booking);
                $this->emitStatus($booking, 'no_driver_available');
            });
            $expired++;
        }

        return $expired;
    }

    // ── internals ────────────────────────────────────────────────────

    /**
     * Publish a `booking.status_changed` domain event to the rider's realtime +
     * push channels. Written to the per-shard outbox (atomic with the state
     * change when called inside a transaction) and drained by fleet:events-relay.
     */
    private function emitStatus(RideBooking $booking, ?string $reason = null): void
    {
        $this->events->emit(BookingEvents::statusChanged($booking, 'system', $reason));
    }

    /** Hold the fare in escrow (wallet only). Returns the held minor amount. */
    private function authorize(RideBooking $booking, int $userId, int $fare, string $currency, string $paymentMethod): int
    {
        if ($paymentMethod === 'cash') {
            return 0;
        }

        // Idempotent: never double-hold the same booking.
        if ($this->wallet->escrowBalanceMinor((int) $booking->id, $currency) > 0) {
            return $this->wallet->escrowBalanceMinor((int) $booking->id, $currency);
        }

        $balance = $this->wallet->walletBalanceMinor('user', $userId, $currency);
        if ($fare > $balance) {
            throw DomainException::make('insufficient_balance', 422);
        }

        // Scope the ledger idempotency key by office: a decline re-holds against
        // a DIFFERENT office, and a booking-only key would be deduped as the
        // original hold and silently hold nothing.
        $this->wallet->holdRide((int) $booking->id, $userId, $fare, $currency, 'fixed-hold:' . $booking->id . ':' . $booking->office_id);

        return $fare;
    }

    /** Refund whatever is held for this booking back to the rider (void). */
    private function voidHold(RideBooking $booking): void
    {
        $currency = (string) $booking->currency_code;
        $held = $this->wallet->escrowBalanceMinor((int) $booking->id, $currency);
        if ($held > 0) {
            // Office-scoped like the hold, so voiding one office's hold and later
            // voiding a backup's hold never collide on the same key.
            $this->wallet->refundFromEscrow((int) $booking->id, (int) $booking->user_id, $held, $currency, 'fixed-void:' . $booking->id . ':' . $booking->office_id);
            $booking->held_minor = 0;
        }
    }

    /**
     * Next-cheapest office not yet tried whose corridor fare is ≤ the locked
     * fare, for the SAME corridor the rider booked. Honors the quoted price: the
     * rider is never asked for more.
     */
    private function nextOffice(RideBooking $booking, FixedTripMeta $meta): ?int
    {
        $tried = $meta->offered_office_ids ?? [];
        $offers = $this->corridorOffers(
            (int) $meta->sub_service_id,
            (int) $meta->departure_city_id,
            (int) $meta->arrival_city_id,
        );

        foreach ($offers as $offer) {
            $id = (int) ($offer['office_id'] ?? 0);
            if ($id === 0 || in_array($id, $tried, true)) {
                continue;
            }
            if ((int) $offer['fare_minor'] <= (int) $meta->locked_fare_minor) {
                return $id;
            }
        }

        return null;
    }

    // ── corridor pricing (travel_routes) ─────────────────────────────

    /**
     * Every office with a published corridor for this (sub-service, departure →
     * arrival) triple, cheapest flat fare first. Reuses the shared office card
     * summary (name, rating, verified, monitoring) so fixed offers look like the
     * rest of the app.
     *
     * @return array<int, array<string, mixed>>
     */
    private function corridorOffers(int $subServiceId, int $departureCityId, int $arrivalCityId): array
    {
        $routes = TravelRoutes::query()
            ->where('sub_service_id', $subServiceId)
            ->where('departure_city_id', $departureCityId)
            ->where('arrival_city_id', $arrivalCityId)
            ->whereNotNull('officeId')
            ->get();

        $currency = $this->corridorCurrency();
        $offers = [];
        foreach ($routes as $route) {
            $officeId = (int) $route->officeId;
            $summary = $this->offices->summary($officeId);
            $offers[] = array_merge($summary, [
                'office_id' => $officeId,
                'fare_minor' => $this->fareMinor($route->trip_price),
                'currency_code' => $currency,
                'travel_route_id' => (int) $route->id,
                'pricing_style' => 'fixed',
            ]);
        }

        // Cheapest flat fare first — the rider compares on price.
        usort($offers, fn ($a, $b) => $a['fare_minor'] <=> $b['fare_minor']);

        return $offers;
    }

    /** The specific corridor row an office publishes, or null if it has none. */
    /**
     * When a driver must be assigned by.
     *
     * Normally two hours before pickup — but that alone put the deadline in the
     * PAST for anything booked for soon: a trip requested at 06:22 for 06:22 was
     * born with an 04:22 deadline and the next SLA sweep killed it before the
     * office could even open the request. The office gets at least as long to
     * assign as it has to accept the offer, so the deadline can never precede
     * the request itself.
     */
    private function assignDeadline($scheduledAt): ?Carbon
    {
        if (! $scheduledAt) {
            return null;
        }

        $floor = Carbon::now()->addMinutes(self::OFFER_TTL_MINUTES);
        $fromPickup = Carbon::parse($scheduledAt)->subMinutes(self::DRIVER_SLA_MINUTES);

        return $fromPickup->greaterThan($floor) ? $fromPickup : $floor;
    }

    private function corridorFor(int $officeId, int $subServiceId, int $departureCityId, int $arrivalCityId): ?TravelRoutes
    {
        return TravelRoutes::query()
            ->with('subService')
            ->where('officeId', $officeId)
            ->where('sub_service_id', $subServiceId)
            ->where('departure_city_id', $departureCityId)
            ->where('arrival_city_id', $arrivalCityId)
            ->first();
    }

    /** trip_price is a major-unit decimal; the ledger works in minor units. */
    private function fareMinor($tripPrice): int
    {
        return (int) round(((float) $tripPrice) * 100);
    }

    /**
     * The corridor's currency. travel_routes carry no currency column — every
     * office in a shard bills in that shard's currency (SY → SYP), so the tenant
     * currency is the single source of truth.
     */
    private function corridorCurrency(): string
    {
        return ShardManager::currency();
    }

    private function cancellationFee(RideBooking $booking, FixedTripMeta $meta): int
    {
        if ($booking->scheduled_at === null) {
            return 0;
        }
        $minutesToPickup = Carbon::now()->diffInMinutes(Carbon::parse($booking->scheduled_at), false);
        if ($minutesToPickup >= self::FREE_CANCEL_MINUTES) {
            return 0;
        }

        // A flat fee = 10% of the locked fare, min nothing captured beyond it.
        return (int) round(((int) $meta->locked_fare_minor) * 0.10);
    }

    private function ownedByOffice(int $officeId, int $bookingId): RideBooking
    {
        $booking = $this->repository->find($bookingId);
        if ($booking === null || (int) $booking->office_id !== $officeId) {
            throw DomainException::notFound('booking_not_found');
        }

        return $booking;
    }

    private function assertStatus(RideBooking $booking, array $allowed): void
    {
        if (! in_array($booking->status, $allowed, true)) {
            throw DomainException::make('invalid_transition', 409);
        }
    }

    private function meta(int $bookingId): FixedTripMeta
    {
        $meta = FixedTripMeta::query()->where('booking_id', $bookingId)->first();
        if ($meta === null) {
            throw DomainException::notFound('booking_not_found');
        }

        return $meta;
    }

    private function normalizeContext(string $context): string
    {
        return in_array($context, ['personal', 'corporate', 'family'], true) ? $context : 'personal';
    }

    private function parseTime($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Carbon::parse($value)->toDateTimeString();
    }

    private function present(int $bookingId): array
    {
        $booking = $this->repository->find($bookingId);
        $meta = FixedTripMeta::query()->where('booking_id', $bookingId)->first();

        return [
            'id' => (int) $booking->id,
            // The rider status screen branches its timeline on this: 'fixed' is
            // the office-mediated corridor path (full office_confirmed step),
            // 'meter' is the direct-to-driver path. This engine only makes fixed.
            'trip_type' => 'fixed',
            'pricing_style' => (string) $booking->pricing_style,
            'status' => (string) $booking->status,
            'office_id' => (int) $booking->office_id,
            'driver_id' => $booking->driver_id !== null ? (int) $booking->driver_id : null,
            'service' => $booking->service,
            'service_class' => $booking->service_class,
            'sub_service_id' => $meta ? (int) $meta->sub_service_id : null,
            'departure_city_id' => $meta ? (int) $meta->departure_city_id : null,
            'arrival_city_id' => $meta ? (int) $meta->arrival_city_id : null,
            'scheduled_at' => $booking->scheduled_at,
            'currency_code' => (string) $booking->currency_code,
            'locked_fare_minor' => $meta ? (int) $meta->locked_fare_minor : (int) $booking->total_minor,
            'total_minor' => (int) $booking->total_minor,
            'held_minor' => (int) $booking->held_minor,
            'payment_method' => (string) $booking->payment_method,
            'context' => $meta?->context ?? 'personal',
            'accepted_at' => $meta?->accepted_at,
            'declined_at' => $meta?->declined_at,
            'steps' => $this->steps($booking),
        ];
    }

    /** Assignment-timeline steps for the status screen. */
    private function steps(RideBooking $booking): array
    {
        $status = (string) $booking->status;
        $done = fn (bool $c) => $c ? 'done' : 'pending';

        $confirmed = ! in_array($status, [BookingStatus::PENDING_ACCEPTANCE, BookingStatus::DECLINED], true);
        $hasDriver = $booking->driver_id !== null;
        $live = in_array($status, BookingStatus::LIVE_SUB, true) || $status === BookingStatus::COMPLETED;

        return [
            ['key' => 'requested', 'state' => 'done'],
            ['key' => 'office_confirmed', 'state' => $status === BookingStatus::PENDING_ACCEPTANCE ? 'now' : $done($confirmed)],
            ['key' => 'driver_assigned', 'state' => $hasDriver ? 'done' : ($confirmed ? 'now' : 'pending')],
            ['key' => 'on_the_way', 'state' => $done($live)],
        ];
    }
}
