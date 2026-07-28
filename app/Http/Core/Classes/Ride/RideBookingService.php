<?php

namespace App\Http\Core\Classes\Ride;

use App\Http\Core\Classes\Account\PromoService;
use App\Http\Core\Classes\Dispatch\DispatchService;
use App\Http\Core\Classes\Dispatch\Geo;
use App\Http\Core\Classes\Event\BookingEvents;
use App\Http\Core\Classes\Event\EventBus;
use App\Http\Core\Classes\Ledger\FleetWalletService;
use App\Http\Core\Classes\Pricing\PricingService;
use App\Http\Core\Classes\Pricing\TariffResolver;
use App\Http\Core\Classes\Settings\AppSettings;
use App\Http\Core\Const\Dispatch\DispatchStatus;
use App\Http\Core\Const\Dispatch\OfferStatus;
use App\Http\Core\Const\Event\Channel;
use App\Http\Core\Const\Ledger\OwnerType;
use App\Http\Core\Const\Ride\BookingSource;
use App\Http\Core\Const\Ride\BookingStatus;
use App\Http\Core\Exceptions\DomainException;
use App\Http\Core\Repositories\Ride\RideBookingRepository;
use App\Models\CommissionSnapshot;
use App\Models\DispatchJob;
use App\Models\DispatchOffer;
use App\Models\Driver;
use App\Models\DriverPresence;
use App\Models\RideBooking;
use App\Models\RideRating;
use RuntimeException;

class RideBookingService
{
    private const CANCEL_FEE_MINOR = 500;

    public function __construct(
        private RideBookingRepository $repository,
        private TariffResolver $tariffs,
        private PricingService $pricing,
        private FleetWalletService $wallet,
        private DispatchService $dispatch,
        private OfficeReadModel $offices,
        private ?EventBus $events = null,
        private ?PromoService $promos = null
    ) {
        $this->promos ??= new PromoService();
    }

    public function create(int $userId, array $in, string $idempotencyKey): array
    {
        if ($idempotencyKey !== '') {
            $existing = $this->repository->findByIdempotencyKey($userId, $idempotencyKey);

            if ($existing !== null) {
                return $this->present($existing);
            }
        }

        // A blocked rider keeps read access but cannot open new rides. Additive
        // guard — normal riders (isActive) are unaffected; only a staff-blocked
        // account is rejected, with a distinct code the app can surface.
        // Fail-open: a lookup error never blocks a booking (block is only applied
        // when isActive=0 is positively confirmed).
        try {
            $isActive = \App\Models\User::query()->whereKey($userId)->value('isActive');
        } catch (\Throwable $e) {
            $isActive = null;
        }
        if ($isActive !== null && ! (bool) $isActive) {
            throw DomainException::make('account_blocked', 403);
        }

        $officeId = (int) ($in['office_id'] ?? 0);
        $serviceClass = (string) ($in['service_class'] ?? '');
        $service = (string) ($in['service'] ?? 'ride');
        $subServiceId = (int) ($in['sub_service_id'] ?? 0);
        // Meter pricing comes from the sub-service catalog when the booking names
        // one; otherwise the per-office ServiceTariff (backward compatible).
        $tariff = $this->tariffs->forOfficeServiceOrSub($officeId, $subServiceId ?: null, $service, $serviceClass);

        if ($tariff === null) {
            throw DomainException::notFound('tariff_not_found');
        }

        [$pickupLat, $pickupLng] = [(float) $in['pickup']['lat'], (float) $in['pickup']['lng']];
        [$dropLat, $dropLng] = [(float) $in['dropoff']['lat'], (float) $in['dropoff']['lng']];

        // Mid-route stops (added during booking creation) — priced into the leg
        // distance pickup → stops → dropoff.
        $stops = is_array($in['stops'] ?? null) ? array_values($in['stops']) : [];

        $distance = (int) ($in['distance_m'] ?? 0);
        if ($distance <= 0) {
            $points = [[$pickupLat, $pickupLng]];
            foreach ($stops as $s) {
                $points[] = [(float) $s['lat'], (float) $s['lng']];
            }
            $points[] = [$dropLat, $dropLng];

            $sum = 0;
            for ($i = 1; $i < count($points); $i++) {
                $sum += Geo::haversineMeters($points[$i - 1][0], $points[$i - 1][1], $points[$i][0], $points[$i][1]);
            }
            $distance = (int) round($sum);
        }

        $duration = (int) ($in['duration_s'] ?? 0);
        if ($duration <= 0) {
            $duration = (int) round($distance / 8);
        }

        $currency = (string) $tariff['currency_code'];
        $quote = $this->pricing->quote($tariff, $distance, $duration);
        $fare = (int) $quote['fare_minor'];

        if ($fare <= 0) {
            throw new RuntimeException('fare must be positive');
        }

        $promo = isset($in['promo_code']) && trim((string) $in['promo_code']) !== '' ? (string) $in['promo_code'] : null;
        // Numeric `service` doubles as the coupon-scoping service id; a word
        // service (e.g. "ride") has no numeric id, so the coupon applies broadly.
        $promoServiceId = ctype_digit($service) ? (int) $service : null;
        $promoEval = $promo !== null
            ? $this->promos->evaluate($promo, $fare, $promoServiceId)
            : ['valid' => false, 'couponId' => null, 'discountMinor' => 0];
        $discount = (int) ($promoEval['discountMinor'] ?? 0);
        // Only persist the code when it actually applied — a typo shouldn't be
        // stamped on the booking as if it discounted anything.
        $promo = ($promoEval['valid'] ?? false) ? ($promoEval['code'] ?? $promo) : null;
        $total = max(0, $fare - $discount);
        $paymentMethod = strtolower((string) ($in['payment_method'] ?? 'wallet'));

        $booking = $this->repository->transaction(function () use (
            $userId, $officeId, $in, $serviceClass, $subServiceId, $tariff, $quote, $currency, $fare,
            $discount, $total, $paymentMethod, $promo, $idempotencyKey,
            $pickupLat, $pickupLng, $dropLat, $dropLng, $distance, $duration, $stops
        ) {
            $booking = $this->repository->create([
                'user_id' => $userId,
                'office_id' => $officeId,
                'source' => (string) ($in['source'] ?? BookingSource::RIDER),
                'service' => (string) ($in['service'] ?? 'ride'),
                'service_class' => $serviceClass,
                'sub_service_id' => $subServiceId ?: null,
                'pricing_style' => (string) $quote['pricing_style'],
                'status' => BookingStatus::MATCHING,
                'pickup_lat' => $pickupLat,
                'pickup_lng' => $pickupLng,
                'pickup_note' => $in['pickup']['note'] ?? null,
                'pickup_title' => $in['pickup']['title'] ?? null,
                'dropoff_lat' => $dropLat,
                'dropoff_lng' => $dropLng,
                'dropoff_title' => $in['dropoff']['title'] ?? null,
                'stops' => $stops !== [] ? $stops : null,
                'distance_m' => $distance,
                'duration_s' => $duration,
                'currency_code' => $currency,
                'fare_minor' => $fare,
                'discount_minor' => $discount,
                'total_minor' => $total,
                'held_minor' => 0,
                'payment_method' => $paymentMethod,
                'promo_code' => $promo,
                'idempotency_key' => $idempotencyKey !== '' ? $idempotencyKey : null,
            ]);

            if ($paymentMethod !== 'cash' && $total > 0) {
                $balance = $this->wallet->lockWalletBalanceMinor(OwnerType::USER, $userId, $currency);

                if ($total > $balance) {
                    throw DomainException::make('insufficient_funds');
                }

                $this->wallet->holdRide($booking->id, $userId, $total, $currency, 'hold:' . $booking->id);
                $booking->held_minor = $total;
                $this->repository->save($booking);
            }

            // Announce the search BEFORE offering it to anyone. The outbox is
            // published in insertion order, so emitting after `offerWave` would
            // let drivers see the request before the rider learns it is being
            // matched. Emitting inside the transaction also makes the event
            // atomic with the booking: a rollback takes the event with it.
            $this->emitStatus($booking);
            $this->emitOrderCreated($booking);

            $this->dispatch->createJob($booking->id, $officeId, $serviceClass, $pickupLat, $pickupLng);
            $this->dispatch->offerWave($booking->id);

            return $booking;
        });

        // Count the redemption once the booking is committed, so per-code usage
        // limits hold. Best-effort — bookkeeping never voids a made booking.
        if (($promoEval['valid'] ?? false) && ($promoEval['couponId'] ?? null)) {
            $this->promos->recordUse((int) $promoEval['couponId'], $userId);
        }

        return $this->present($booking->fresh());
    }

    public function get(int $userId, int $bookingId): array
    {
        return $this->present($this->owned($userId, $bookingId));
    }

    public function cancel(int $userId, int $bookingId, ?string $reason): array
    {
        $booking = $this->owned($userId, $bookingId);
        $job = $this->job($bookingId);
        $status = $this->effectiveStatus($booking, $job);

        if (in_array($status, BookingStatus::TERMINAL, true)) {
            throw DomainException::conflict('not_cancellable');
        }

        $held = $this->wallet->escrowBalanceMinor($bookingId, $booking->currency_code);

        if ($held > 0) {
            if (strtolower((string) $booking->payment_method) === 'office_wallet') {
                $this->wallet->refundEscrowToOffice($bookingId, (int) $booking->office_id, $held, $booking->currency_code, 'cancel-refund:' . $bookingId);
            } else {
                $this->wallet->refundFromEscrow($bookingId, $userId, $held, $booking->currency_code, 'cancel-refund:' . $bookingId);
            }
        }

        $this->dispatch->cancelJob($bookingId);

        $booking->status = BookingStatus::CANCELLED;
        $booking->cancelled_at = now();
        $booking->cancel_reason = $reason;
        $booking->held_minor = 0;
        $this->repository->save($booking);

        $this->emitStatus($booking, BookingSource::RIDER, $reason);

        return $this->present($booking);
    }

    public function changeOffice(int $userId, int $bookingId, int $newOfficeId): array
    {
        $booking = $this->owned($userId, $bookingId);
        $job = $this->job($bookingId);

        if ($this->effectiveStatus($booking, $job) !== BookingStatus::MATCHING) {
            throw DomainException::conflict('already_assigned');
        }

        $tariff = $this->tariffs->forOfficeService($newOfficeId, $booking->service, $booking->service_class);

        if ($tariff === null) {
            throw DomainException::notFound('tariff_not_found');
        }

        $currency = (string) $tariff['currency_code'];
        $quote = $this->pricing->quote($tariff, $booking->distance_m, $booking->duration_s);
        $fare = (int) $quote['fare_minor'];
        // Re-apply the booking's existing coupon against the new office's fare.
        $promoServiceId = ctype_digit((string) $booking->service) ? (int) $booking->service : null;
        $promoEval = $booking->promo_code
            ? $this->promos->evaluate((string) $booking->promo_code, $fare, $promoServiceId)
            : ['discountMinor' => 0];
        $discount = (int) ($promoEval['discountMinor'] ?? 0);
        $total = max(0, $fare - $discount);

        $revision = (int) $booking->change_revision + 1;

        $this->repository->transaction(function () use (
            $booking, $userId, $bookingId, $newOfficeId, $currency, $quote, $fare, $discount, $total, $revision
        ) {
            if ($booking->payment_method !== 'cash') {
                $held = $this->wallet->escrowBalanceMinor($bookingId, $booking->currency_code);

                if ($held > 0) {
                    $this->wallet->refundFromEscrow($bookingId, $userId, $held, $booking->currency_code, 'change-office-refund:' . $bookingId . ':' . $revision);
                }

                if ($total > 0) {
                    $balance = $this->wallet->lockWalletBalanceMinor(OwnerType::USER, $userId, $currency);

                    if ($total > $balance) {
                        throw DomainException::make('insufficient_funds');
                    }

                    $this->wallet->holdRide($bookingId, $userId, $total, $currency, 'hold:' . $bookingId . ':' . $revision);
                }
            }

            DispatchJob::query()->where('booking_id', $bookingId)->update([
                'office_id' => $newOfficeId,
                'status' => DispatchStatus::PENDING,
                'wave' => 0,
                'assigned_driver_id' => null,
                'assigned_at' => null,
            ]);

            DispatchOffer::query()->where('booking_id', $bookingId)
                ->where('status', OfferStatus::OFFERED)
                ->update(['status' => OfferStatus::EXPIRED]);

            $booking->office_id = $newOfficeId;
            $booking->pricing_style = (string) $quote['pricing_style'];
            $booking->currency_code = $currency;
            $booking->fare_minor = $fare;
            $booking->discount_minor = $discount;
            $booking->total_minor = $total;
            $booking->held_minor = $booking->payment_method !== 'cash' ? $total : 0;
            $booking->change_revision = $revision;
            $booking->status = BookingStatus::MATCHING;
            $this->repository->save($booking);

            $this->dispatch->offerWave($bookingId);
        });

        $this->emitStatus($booking->fresh());

        return $this->present($booking->fresh());
    }

    public function history(int $userId, string $filter, ?int $cursorId, int $limit): array
    {
        $rows = $this->repository->history($userId, $filter, $cursorId, $limit);
        $hasMore = $rows->count() > $limit;
        $items = $rows->take($limit);

        return [
            'data' => $items->map(fn (RideBooking $b) => $this->historyRow($b))->values()->all(),
            'meta' => [
                'next_cursor' => $hasMore ? (string) $items->last()->id : null,
                'has_more' => $hasMore,
            ],
        ];
    }

    public function receipt(int $userId, int $bookingId): array
    {
        $booking = $this->owned($userId, $bookingId);
        $job = $this->job($bookingId);
        $office = $this->offices->summary((int) $booking->office_id);

        $driver = null;

        if ($job !== null && $job->assigned_driver_id !== null) {
            $driver = ['id' => (int) $job->assigned_driver_id, 'name' => null, 'vehicle' => null];
        }

        $breakdown = [['label' => 'fare', 'amount_minor' => (int) $booking->fare_minor]];

        if ((int) $booking->discount_minor > 0) {
            $breakdown[] = ['label' => 'discount', 'amount_minor' => -1 * (int) $booking->discount_minor];
        }

        return [
            'booking_id' => (int) $booking->id,
            'status' => $this->effectiveStatus($booking, $job),
            'route' => [
                'from' => $booking->pickup_title,
                'to' => $booking->dropoff_title,
                'at' => optional($booking->created_at)->toIso8601String(),
                'distance_m' => (int) $booking->distance_m,
                'duration_s' => (int) $booking->duration_s,
                'pickup' => ['lat' => (float) $booking->pickup_lat, 'lng' => (float) $booking->pickup_lng],
                'dropoff' => ['lat' => (float) $booking->dropoff_lat, 'lng' => (float) $booking->dropoff_lng],
            ],
            'office' => $office,
            'driver' => $driver,
            'pricing_style' => $booking->pricing_style,
            'fare_breakdown' => $breakdown,
            'discount_minor' => (int) $booking->discount_minor,
            'total_minor' => (int) $booking->total_minor,
            'currency_code' => $booking->currency_code,
            'payment_method' => $booking->payment_method,
            'support_tickets' => [],
        ];
    }

    public function share(int $userId, int $bookingId): array
    {
        $booking = $this->owned($userId, $bookingId);

        return ['share_url' => rtrim((string) config('app.url'), '/') . '/t/' . $booking->id . '-' . $this->shareToken((int) $booking->id)];
    }

    public function sharedView(int $bookingId, string $token): ?array
    {
        if (! hash_equals($this->shareToken($bookingId), $token)) {
            return null;
        }

        $booking = $this->repository->find($bookingId);

        if ($booking === null) {
            return null;
        }

        $job = $this->job($bookingId);

        return [
            'booking_id' => (int) $booking->id,
            'status' => $this->effectiveStatus($booking, $job),
            'from' => $booking->pickup_title,
            'to' => $booking->dropoff_title,
            'at' => optional($booking->created_at)->toIso8601String(),
            'office' => $this->offices->summary((int) $booking->office_id)['name'],
            'currency_code' => $booking->currency_code,
        ];
    }

    private function shareToken(int $bookingId): string
    {
        return substr(hash_hmac('sha256', (string) $bookingId, (string) config('app.key')), 0, 24);
    }

    public function activeFor(int $userId): ?array
    {
        $active = $this->repository->activeForUser($userId);

        return $active !== null ? $this->present($active) : null;
    }

    private function historyRow(RideBooking $booking): array
    {
        $office = $this->offices->summary((int) $booking->office_id);

        $driverRating = RideRating::query()
            ->where('booking_id', $booking->id)
            ->where('rater_type', 'user')
            ->where('ratee_type', 'driver')
            ->first();

        return [
            'booking_id' => (int) $booking->id,
            'from' => $booking->pickup_title,
            'to' => $booking->dropoff_title,
            'at' => optional($booking->created_at)->toIso8601String(),
            'service' => $booking->service,
            'status' => $this->effectiveStatus($booking, $this->job((int) $booking->id)),
            'office' => ['office_id' => $office['office_id'], 'name' => $office['name'], 'logo_url' => $office['logo_url']],
            'total_minor' => (int) $booking->total_minor,
            'currency_code' => $booking->currency_code,
            'rating_state' => $driverRating !== null ? 'rated' : 'unrated',
            'stars' => $driverRating !== null ? (int) $driverRating->stars : null,
        ];
    }

    /**
     * Give up on a search nobody answered. Ends it exactly like a cancellation
     * — escrow refunded, outstanding offers withdrawn (and those drivers told),
     * rider notified — but attributed to the system, so the rider sees "no
     * driver found" instead of a booking that spins forever.
     *
     * Only ever touches a booking still in MATCHING; anything assigned or
     * already terminal is left alone.
     */
    public function failMatching(int $bookingId, string $reason = 'no_driver_found'): bool
    {
        $booking = RideBooking::query()->find($bookingId);

        if ($booking === null) {
            return false;
        }

        $status = $this->effectiveStatus($booking, $this->job($bookingId));

        if ($status !== BookingStatus::MATCHING) {
            return false;
        }

        $held = $this->wallet->escrowBalanceMinor($bookingId, $booking->currency_code);

        if ($held > 0) {
            if (strtolower((string) $booking->payment_method) === 'office_wallet') {
                $this->wallet->refundEscrowToOffice($bookingId, (int) $booking->office_id, $held, $booking->currency_code, 'search-timeout-refund:' . $bookingId);
            } else {
                $this->wallet->refundFromEscrow($bookingId, (int) $booking->user_id, $held, $booking->currency_code, 'search-timeout-refund:' . $bookingId);
            }
        }

        // Pulls the request off every driver still holding an offer.
        $this->dispatch->cancelJob($bookingId, $reason, BookingSource::SYSTEM);

        $booking->status = BookingStatus::CANCELLED;
        $booking->cancelled_at = now();
        $booking->cancel_reason = $reason;
        $booking->held_minor = 0;
        $this->repository->save($booking);

        $this->emitStatus($booking, BookingSource::SYSTEM, $reason);

        return true;
    }

    private function owned(int $userId, int $bookingId): RideBooking
    {
        $booking = $this->repository->findForUser($bookingId, $userId);

        if ($booking === null) {
            throw DomainException::notFound();
        }

        return $booking;
    }

    private function job(int $bookingId): ?DispatchJob
    {
        return DispatchJob::query()->where('booking_id', $bookingId)->first();
    }

    private function effectiveStatus(RideBooking $booking, ?DispatchJob $job): string
    {
        if (in_array($booking->status, BookingStatus::TERMINAL, true)) {
            return $booking->status;
        }

        $settled = CommissionSnapshot::query()->where('booking_id', $booking->id)->exists();

        if ($settled) {
            return BookingStatus::COMPLETED;
        }

        if ($job !== null && $job->status === DispatchStatus::ASSIGNED) {
            return in_array($booking->status, BookingStatus::LIVE_SUB, true) ? $booking->status : BookingStatus::ASSIGNED;
        }

        if (in_array($booking->status, BookingStatus::LIVE_SUB, true)) {
            return $booking->status;
        }

        return BookingStatus::MATCHING;
    }

    private function present(RideBooking $booking): array
    {
        $job = $this->job($booking->id);
        $status = $this->effectiveStatus($booking, $job);

        $driver = null;

        if ($job !== null && $job->assigned_driver_id !== null) {
            $presence = DriverPresence::query()->where('driver_id', $job->assigned_driver_id)->first();

            // The minimal driver, always safe to return.
            $driver = [
                'id' => (int) $job->assigned_driver_id,
                'name' => null,
                'rating' => null,
                'vehicle' => null,
                'lat' => $presence !== null ? (float) $presence->lat : null,
                'lng' => $presence !== null ? (float) $presence->lng : null,
                'eta_min' => null,
            ];

            // Enrich with the driver's real name + vehicle. Purely DECORATIVE, so
            // ANY lookup failure — a missing row, a DB hiccup — must degrade to the
            // minimal driver above and never 500 the whole booking detail. It used
            // to guard only a null row, so a failed query (e.g. an unmigrated
            // drivers table) propagated and broke `GET /bookings/{id}` outright.
            try {
                $driverModel = Driver::query()->find($job->assigned_driver_id);
                if ($driverModel !== null) {
                    $vehicle = $driverModel->vehicleId !== null ? $driverModel->vehicle : null;
                    $driver['name'] = trim(((string) $driverModel->firstName) . ' ' . ((string) $driverModel->lastName));
                    $driver['rating'] = (float) ($driverModel->rating ?? 0);
                    $driver['vehicle'] = $vehicle !== null ? [
                        'model' => trim(((string) $vehicle->vehicleBrand) . ' ' . ((string) $vehicle->model)),
                        'plate' => (string) $vehicle->plate,
                        // The rider app spells this key `colour`.
                        'colour' => (string) $vehicle->color,
                        'class_label' => $booking->service_class !== null ? strtoupper((string) $booking->service_class) : null,
                    ] : null;
                }
            } catch (\Throwable $e) {
                // Decorative enrichment unavailable — keep the minimal driver.
            }
        }

        return [
            'booking_id' => (int) $booking->id,
            'status' => $status,
            'office' => ['office_id' => (int) $booking->office_id],
            'service' => $booking->service,
            'service_class' => $booking->service_class,
            'pricing_style' => $booking->pricing_style,
            'currency_code' => $booking->currency_code,
            'fare_minor' => (int) $booking->fare_minor,
            'discount_minor' => (int) $booking->discount_minor,
            'total_minor' => (int) $booking->total_minor,
            'held_minor' => (int) $booking->held_minor,
            'pickup' => ['lat' => (float) $booking->pickup_lat, 'lng' => (float) $booking->pickup_lng, 'note' => $booking->pickup_note, 'title' => $booking->pickup_title],
            'dropoff' => ['lat' => (float) $booking->dropoff_lat, 'lng' => (float) $booking->dropoff_lng, 'title' => $booking->dropoff_title],
            'driver' => $driver,
            'meter' => null,
            'cancel' => ['free_until' => 'assigned', 'fee_minor' => AppSettings::int('cancellation_fee_minor', self::CANCEL_FEE_MINOR)],
            'channel' => Channel::booking((int) $booking->id),
        ];
    }

    private function emitStatus(RideBooking $booking, ?string $source = null, ?string $reason = null): void
    {
        if ($this->events === null) {
            return;
        }

        $this->events->emit(BookingEvents::statusChanged($booking, $source, $reason));
    }

    private function emitOrderCreated(RideBooking $booking): void
    {
        if ($this->events === null) {
            return;
        }

        $this->events->emit(BookingEvents::orderCreated($booking));
    }

}
