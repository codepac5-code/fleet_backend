<?php

namespace App\Http\Core\Classes\Ride;

use App\Http\Core\Classes\Dispatch\Geo;
use App\Http\Core\Classes\Pricing\PricingService;
use App\Http\Core\Classes\Pricing\TariffResolver;
use App\Http\Core\Const\Ride\BookingStatus;
use App\Http\Core\Const\Ride\ServiceCatalog;
use App\Http\Core\Exceptions\DomainException;
use App\Http\Core\Repositories\Ride\RideBookingRepository;
use App\Models\DispatchJob;
use App\Models\RideBooking;
use Illuminate\Support\Carbon;

class ScheduledService
{
    private const CANCEL_FEE_MINOR = 1500;
    private const FREE_CANCEL_HOURS = 2;

    public function __construct(
        private RideBookingRepository $repository,
        private TariffResolver $tariffs,
        private PricingService $pricing,
        private OfficeReadModel $offices,
        private \App\Http\Core\GeoServices\RouteEstimator $routes = new \App\Http\Core\GeoServices\RouteEstimator(),
        private \App\Http\Core\Classes\Pricing\MeterPricingService $meter = new \App\Http\Core\Classes\Pricing\MeterPricingService(),
    ) {
    }

    public function offers(string $service, string $serviceClass, float $pLat, float $pLng, float $dLat, float $dLng, ?int $subServiceId = null): array
    {
        if (! ServiceCatalog::isService($service)) {
            throw DomainException::make('invalid_service');
        }

        [$distance, $duration] = $this->route($pLat, $pLng, $dLat, $dLng);
        $offers = [];

        foreach ($this->tariffs->offeringOfficeIds($service, $serviceClass) as $officeId) {
            $tariff = $this->tariffs->forOfficeService($officeId, $service, $serviceClass);

            if ($tariff === null) {
                continue;
            }

            $summary = $this->offices->summary($officeId);

            // Meter pricing (open + per-km + per-minute from the sub-service /
            // office override) when a sub-service is chosen; otherwise the legacy
            // tariff quote. The tariff still gates WHICH offices serve the route.
            $priced = $subServiceId !== null
                ? $this->meter->quote($officeId, $subServiceId, $distance, $duration)
                : $this->pricing->quote($tariff, $distance, $duration);

            $offers[] = array_merge($summary, [
                'fare_minor' => (int) $priced['fare_minor'],
                'currency_code' => $priced['currency_code'] ?? $tariff['currency_code'],
                'pricing_style' => $subServiceId !== null ? 'meter' : ServiceCatalog::style($service),
                'free_wait_min' => $service === ServiceCatalog::TRAVEL ? 60 : 30,
                'perks' => $this->perks($service),
            ]);
        }

        usort($offers, fn ($a, $b) => [$a['fare_minor'], -1 * (float) $a['rating']] <=> [$b['fare_minor'], -1 * (float) $b['rating']]);

        return ['offers' => $offers];
    }

    public function create(int $userId, array $in): array
    {
        $officeId = (int) ($in['office_id'] ?? 0);
        $service = (string) ($in['service'] ?? 'travel');
        $serviceClass = (string) ($in['service_class'] ?? '');
        $subServiceId = isset($in['sub_service_id']) ? (int) $in['sub_service_id'] : null;
        $scheduledAt = $this->parseTime($in['scheduled_at'] ?? null);

        $tariff = $this->tariffs->forOfficeService($officeId, $service, $serviceClass);

        // Meter pricing comes from the sub-service, not a tariff — so a meter
        // booking only needs a tariff to fall through the legacy path. The tariff
        // is required ONLY when no sub-service was chosen.
        if ($tariff === null && $subServiceId === null) {
            throw DomainException::notFound('tariff_not_found');
        }

        [$pLat, $pLng] = [(float) $in['pickup']['lat'], (float) $in['pickup']['lng']];
        [$dLat, $dLng] = [(float) $in['dropoff']['lat'], (float) $in['dropoff']['lng']];
        [$distance, $duration] = $this->route($pLat, $pLng, $dLat, $dLng);

        // Meter (open+km+min from the sub-service) when chosen; else legacy tariff.
        $quote = $subServiceId !== null
            ? $this->meter->quote($officeId, $subServiceId, $distance, $duration)
            : $this->pricing->quote($tariff, $distance, $duration);
        $fare = (int) $quote['fare_minor'];
        $currency = (string) ($quote['currency_code'] ?? $tariff['currency_code']);

        $booking = $this->repository->create([
            'user_id' => $userId,
            'office_id' => $officeId,
            'service' => $service,
            'service_class' => $serviceClass,
            'pricing_style' => (string) $quote['pricing_style'],
            'status' => BookingStatus::SCHEDULED,
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
            'distance_m' => $distance,
            'duration_s' => $duration,
            'currency_code' => $currency,
            'fare_minor' => $fare,
            'discount_minor' => 0,
            'total_minor' => $fare,
            'payment_method' => strtolower((string) ($in['payment_method'] ?? 'wallet')),
        ]);

        return $this->present($booking);
    }

    public function list(int $userId): array
    {
        return $this->repository->scheduledForUser($userId)
            ->map(fn (RideBooking $b) => $this->present($b))
            ->all();
    }

    public function show(int $userId, int $bookingId): array
    {
        $booking = $this->owned($userId, $bookingId);

        return array_merge($this->present($booking), ['timeline' => $this->timeline($booking)]);
    }

    public function update(int $userId, int $bookingId, array $attrs): array
    {
        $booking = $this->owned($userId, $bookingId);

        if ($booking->status !== BookingStatus::SCHEDULED) {
            throw DomainException::conflict('not_editable');
        }

        if (array_key_exists('scheduled_at', $attrs) && $attrs['scheduled_at'] !== null) {
            $booking->scheduled_at = $this->parseTime($attrs['scheduled_at']);
        }

        foreach (['passengers', 'luggage', 'flight_no'] as $field) {
            if (array_key_exists($field, $attrs)) {
                $booking->{$field} = $attrs[$field];
            }
        }

        $this->repository->save($booking);

        return $this->present($booking);
    }

    public function cancel(int $userId, int $bookingId): array
    {
        $booking = $this->owned($userId, $bookingId);

        if (in_array($booking->status, BookingStatus::TERMINAL, true)) {
            throw DomainException::conflict('not_cancellable');
        }

        $booking->status = BookingStatus::CANCELLED;
        $booking->cancelled_at = now();
        $this->repository->save($booking);

        return $this->present($booking);
    }

    private function owned(int $userId, int $bookingId): RideBooking
    {
        $booking = $this->repository->findForUser($bookingId, $userId);

        if ($booking === null) {
            throw DomainException::notFound();
        }

        return $booking;
    }

    private function timeline(RideBooking $booking): array
    {
        $assigned = DispatchJob::query()
            ->where('booking_id', $booking->id)
            ->whereNotNull('assigned_driver_id')
            ->exists();

        return [
            ['step' => 'booking_confirmed', 'state' => 'done'],
            ['step' => 'office_assigned', 'state' => 'done'],
            ['step' => 'driver_pending', 'state' => $assigned ? 'done' : 'current'],
            ['step' => 'driver_enroute', 'state' => $assigned ? 'current' : 'pending'],
        ];
    }

    private function present(RideBooking $booking): array
    {
        return [
            'booking_id' => (int) $booking->id,
            'id' => (int) $booking->id,
            'status' => $booking->status,
            // A scheduled trip is 'meter' (direct-to-driver, meter fare) unless it
            // was priced as a fixed corridor. The rider status timeline branches
            // on this — a meter trip has no office-acceptance step.
            'trip_type' => $booking->pricing_style === 'fixed' ? 'fixed' : 'meter',
            'scheduled_at' => optional($booking->scheduled_at)->toIso8601String(),
            'service' => $booking->service,
            'service_class' => $booking->service_class,
            'pricing_style' => $booking->pricing_style,
            'currency_code' => $booking->currency_code,
            'fare_minor' => (int) $booking->fare_minor,
            'total_minor' => (int) $booking->total_minor,
            'office' => $this->offices->summary((int) $booking->office_id),
            'passengers' => $booking->passengers !== null ? (int) $booking->passengers : null,
            'luggage' => $booking->luggage !== null ? (int) $booking->luggage : null,
            'flight_no' => $booking->flight_no,
            'steps' => $this->steps($booking),
            'cancel' => ['free_until_hours' => self::FREE_CANCEL_HOURS, 'fee_minor' => self::CANCEL_FEE_MINOR],
        ];
    }

    /**
     * Assignment-timeline steps with the SHARED keys the rider's AssignmentTimeline
     * renders (requested → driver_assigned → on_the_way). A meter trip is
     * dispatched straight to a driver, so there is no office-acceptance step.
     */
    private function steps(RideBooking $booking): array
    {
        $status = (string) $booking->status;
        $assigned = $booking->driver_id !== null || DispatchJob::query()
            ->where('booking_id', $booking->id)
            ->whereNotNull('assigned_driver_id')
            ->exists();
        $live = in_array($status, BookingStatus::LIVE_SUB, true) || $status === BookingStatus::COMPLETED;

        return [
            ['key' => 'requested', 'state' => 'done'],
            ['key' => 'driver_assigned', 'state' => $assigned ? 'done' : 'now'],
            ['key' => 'on_the_way', 'state' => $live ? 'now' : 'pending'],
        ];
    }

    private function perks(string $service): array
    {
        return $service === ServiceCatalog::TRAVEL ? ['flight_tracking', 'meet_greet'] : [];
    }

    private function route(float $pLat, float $pLng, float $dLat, float $dLng): array
    {
        // Real driving distance + duration from Google Directions, falling back
        // to the haversine + 8 m/s estimate when the API is unavailable.
        return $this->routes->estimate($pLat, $pLng, $dLat, $dLng);
    }

    private function parseTime($value): Carbon
    {
        if ($value === null || $value === '') {
            throw DomainException::make('validation_failed');
        }

        try {
            return Carbon::parse((string) $value);
        } catch (\Throwable $e) {
            throw DomainException::make('validation_failed');
        }
    }
}
