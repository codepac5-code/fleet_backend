<?php

namespace App\Http\Core\Classes\Ride;

use App\Http\Core\Classes\Dispatch\DispatchService;
use App\Http\Core\Classes\Event\BookingEvents;
use App\Http\Core\Classes\Event\DomainEvent;
use App\Http\Core\Classes\Event\EventBus;
use App\Http\Core\Classes\Event\EventPublisher;
use App\Http\Core\Classes\Ledger\FleetWalletService;
use App\Http\Core\Classes\Pricing\PricingService;
use App\Http\Core\Classes\Pricing\TariffResolver;
use App\Http\Core\Const\Event\Channel;
use App\Http\Core\Const\Event\EventType;
use App\Http\Core\Const\Ride\BookingSource;
use App\Http\Core\Const\Ride\BookingStatus;
use App\Http\Core\Exceptions\DomainException;
use App\Http\Core\Repositories\Dispatch\DispatchJobRepository;
use App\Http\Core\Repositories\Ride\RideBookingRepository;
use App\Models\RideBooking;

class DriverTripService
{
    public function __construct(
        private RideBookingRepository $bookings,
        private DispatchJobRepository $jobs,
        private RideLifecycleService $lifecycle,
        private FleetWalletService $wallet,
        private DispatchService $dispatch,
        private TariffResolver $tariffs,
        private PricingService $pricing,
        private ?EventBus $events = null,
        private ?EventPublisher $realtime = null
    ) {
    }

    public function navigateToPickup(int $driverId, int $bookingId): array
    {
        $booking = $this->assignedBooking($driverId, $bookingId);
        // SCHEDULED = a claimed scheduled/meter trip; ASSIGNED = an office-assigned
        // fixed trip. Both start their pickup drive from here, alongside the
        // instant-dispatch MATCHING/ARRIVING states.
        $this->assertFrom($booking, [
            BookingStatus::SCHEDULED,
            BookingStatus::ASSIGNED,
            BookingStatus::MATCHING,
            BookingStatus::ARRIVING,
        ]);

        return $this->transition($booking, BookingStatus::ARRIVING);
    }

    public function arrived(int $driverId, int $bookingId): array
    {
        $booking = $this->assignedBooking($driverId, $bookingId);
        $this->assertFrom($booking, [
            BookingStatus::SCHEDULED,
            BookingStatus::ASSIGNED,
            BookingStatus::MATCHING,
            BookingStatus::ARRIVING,
            BookingStatus::ARRIVED,
        ]);

        // Stamp the arrival time (once) for on-time-pickup measurement.
        if ($booking->arrived_at === null) {
            $booking->arrived_at = now();
        }

        return $this->transition($booking, BookingStatus::ARRIVED);
    }

    public function startTrip(int $driverId, int $bookingId): array
    {
        $booking = $this->assignedBooking($driverId, $bookingId);
        $this->assertFrom($booking, [BookingStatus::ARRIVED]);

        // Start the live meter fresh: clock from now, distance from zero, no
        // anchor point yet (the first location ping sets it).
        $booking->trip_started_at = now();
        $booking->meter_distance_m = 0;
        $booking->meter_last_lat = null;
        $booking->meter_last_lng = null;

        return $this->transition($booking, BookingStatus::ON_TRIP);
    }

    public function endTrip(int $driverId, int $bookingId, array $meter = []): array
    {
        $booking = $this->assignedBooking($driverId, $bookingId);
        $this->assertFrom($booking, [BookingStatus::ON_TRIP]);

        // Trust the server-accumulated live meter when the client doesn't report
        // its own total — the figure both apps watched is the authoritative one.
        if (!isset($meter['distance_m']) && (int) $booking->meter_distance_m > 0) {
            $meter['distance_m'] = (int) $booking->meter_distance_m;
        }
        if (!isset($meter['duration_s']) && $booking->trip_started_at !== null) {
            $meter['duration_s'] = max(0, now()->getTimestamp() - $booking->trip_started_at->getTimestamp());
        }

        if (isset($meter['distance_m'])) {
            $booking->distance_m = (int) $meter['distance_m'];
        }
        if (isset($meter['duration_s'])) {
            $booking->duration_s = (int) $meter['duration_s'];
        }

        $this->reconcileFinalFare($booking, $meter);
        $this->settle($booking, $driverId);

        $booking->completed_at = now();

        return $this->transition($booking, BookingStatus::COMPLETED);
    }

    private function reconcileFinalFare(RideBooking $booking, array $meter): void
    {
        if ($booking->pricing_style === 'meter' && (isset($meter['distance_m']) || isset($meter['duration_s']))) {
            $tariff = $this->tariffs->forOfficeServiceOrSub((int) $booking->office_id, (int) $booking->sub_service_id ?: null, $booking->service, $booking->service_class);

            if ($tariff !== null) {
                // The customer is charged the LESSER of the actual meter and the
                // fare quoted for the EXPECTED Google route at booking time (still
                // held in total_minor until now). A driver who deviates onto a
                // longer path therefore never inflates the customer's bill; a
                // genuinely shorter trip bills the smaller actual meter.
                $expectedTotal = (int) $booking->total_minor;
                $actualFare = (int) $this->pricing->quote($tariff, (int) $booking->distance_m, (int) $booking->duration_s)['fare_minor'];
                $actualTotal = max(0, $actualFare - (int) $booking->discount_minor);

                $booking->fare_minor = $actualFare;
                $booking->total_minor = min($actualTotal, $expectedTotal);
            }
        }

        if (strtolower((string) $booking->payment_method) === 'cash') {
            return;
        }

        $held = $this->wallet->escrowBalanceMinor((int) $booking->id, $booking->currency_code);
        $final = min((int) $booking->total_minor, $held);
        $excess = $held - $final;

        if ($excess > 0) {
            $this->wallet->refundFromEscrow((int) $booking->id, (int) $booking->user_id, $excess, $booking->currency_code, 'meter-adjust-refund:' . $booking->id);
        }

        $booking->total_minor = $final;
        $booking->held_minor = $final;
    }

    public function confirmPayment(int $driverId, int $bookingId): array
    {
        $booking = $this->assignedBooking($driverId, $bookingId);

        if ($booking->status !== BookingStatus::COMPLETED) {
            $this->assertFrom($booking, [BookingStatus::ON_TRIP]);
            $this->settle($booking, $driverId);
            $booking->completed_at = now();

            return $this->transition($booking, BookingStatus::COMPLETED);
        }

        return $this->present($booking);
    }

    public function cancel(int $driverId, int $bookingId, ?string $reason): array
    {
        $booking = $this->assignedBooking($driverId, $bookingId);

        if (in_array($booking->status, [BookingStatus::ON_TRIP, BookingStatus::COMPLETED, BookingStatus::CANCELLED, BookingStatus::REJECTED], true)) {
            throw DomainException::conflict('not_cancellable');
        }

        $held = $this->wallet->escrowBalanceMinor($bookingId, $booking->currency_code);

        if ($held > 0) {
            $this->wallet->refundFromEscrow($bookingId, (int) $booking->user_id, $held, $booking->currency_code, 'driver-cancel-refund:' . $bookingId);
        }

        $this->dispatch->cancelJob($bookingId);

        $booking->status = BookingStatus::CANCELLED;
        $booking->cancelled_at = now();
        $booking->cancel_reason = $reason;
        $booking->held_minor = 0;
        $this->bookings->save($booking);

        $this->emitStatus($booking, BookingSource::DRIVER, $reason);
        $this->emitJobCancelled($booking, $driverId, $reason);

        return $this->present($booking);
    }

    public function updateLocation(int $driverId, int $bookingId, float $lat, float $lng, ?float $heading = null, ?int $etaSeconds = null): void
    {
        $booking = $this->assignedBooking($driverId, $bookingId);

        // Fold the ping into the server-side meter (no-op unless it's a running
        // metered trip) and persist the running distance/anchor point.
        $meter = (new MeterService($this->tariffs, $this->pricing))->tick($booking, $lat, $lng);
        if ($meter !== null) {
            $this->bookings->save($booking);
        }

        if ($this->realtime === null) {
            return;
        }

        $payload = [
            'booking_id' => $bookingId,
            'driver_id' => $driverId,
            'lat' => $lat,
            'lng' => $lng,
            'heading' => $heading,
            'eta_seconds' => $etaSeconds,
            'at' => now()->toIso8601ZuluString(),
        ];

        $this->realtime->publish(Channel::booking($bookingId), EventType::DRIVER_LOCATION, $payload);
        $this->realtime->publish(Channel::user((int) $booking->user_id), EventType::DRIVER_LOCATION, $payload);

        // Live meter → both the rider and the driver watch the same authoritative figure.
        if ($meter !== null) {
            $this->realtime->publish(Channel::booking($bookingId), EventType::BOOKING_METER, $meter);
            $this->realtime->publish(Channel::user((int) $booking->user_id), EventType::BOOKING_METER, $meter);
        }
    }

    private function assignedBooking(int $driverId, int $bookingId): RideBooking
    {
        $job = $this->jobs->assignmentForDriver($bookingId, $driverId);

        if ($job === null) {
            throw DomainException::make('ride_not_assigned', 403, 'This ride is not assigned to you.');
        }

        $booking = $this->bookings->find($bookingId);

        if ($booking === null) {
            throw DomainException::notFound();
        }

        return $booking;
    }

    private function assertFrom(RideBooking $booking, array $allowed): void
    {
        if (! in_array($booking->status, $allowed, true)) {
            throw DomainException::conflict('invalid_transition');
        }
    }

    private function transition(RideBooking $booking, string $status): array
    {
        $booking->status = $status;
        $this->bookings->save($booking);
        $this->emitStatus($booking);

        return $this->present($booking);
    }

    private function settle(RideBooking $booking, int $driverId): void
    {
        // A zero-total ride has nothing to split three ways, and its escrow (if
        // any was held) was already refunded in reconcileFinalFare. The release
        // asserts a POSITIVE amount, so settling a 0-fare trip 500'd the driver's
        // "end" — e.g. a scheduled meter ride ended before any distance accrued.
        // Nothing to settle → complete the trip cleanly instead of crashing.
        if ((int) $booking->total_minor <= 0) {
            return;
        }

        $this->lifecycle->settle([
            'booking_id' => (int) $booking->id,
            'office_id' => (int) $booking->office_id,
            'driver_id' => $driverId,
            'currency_code' => $booking->currency_code,
            'total_minor' => (int) $booking->total_minor,
            'fare_minor' => (int) $booking->fare_minor,
            'discount_minor' => (int) $booking->discount_minor,
            'pricing_style' => $booking->pricing_style,
            'source' => (string) $booking->source,
        ], (string) $booking->payment_method);
    }

    private function emitStatus(RideBooking $booking, ?string $source = null, ?string $reason = null): void
    {
        if ($this->events === null) {
            return;
        }

        $this->events->emit(BookingEvents::statusChanged($booking, $source, $reason));
    }

    private function emitJobCancelled(RideBooking $booking, int $driverId, ?string $reason): void
    {
        if ($this->events === null) {
            return;
        }

        $this->events->emit(new DomainEvent(
            EventType::DISPATCH_JOB_CANCELLED,
            [Channel::booking((int) $booking->id), Channel::user((int) $booking->user_id), Channel::driver($driverId)],
            ['booking_id' => (int) $booking->id, 'cancelled_by' => 'driver', 'reason' => $reason]
        ));
    }

    private function present(RideBooking $booking): array
    {
        return [
            'booking_id' => (int) $booking->id,
            'status' => $booking->status,
            'service' => $booking->service,
            'service_class' => $booking->service_class,
            'pricing_style' => $booking->pricing_style,
            'currency_code' => $booking->currency_code,
            'total_minor' => (int) $booking->total_minor,
            'payment_method' => $booking->payment_method,
            'pickup' => ['lat' => (float) $booking->pickup_lat, 'lng' => (float) $booking->pickup_lng, 'title' => $booking->pickup_title],
            'dropoff' => ['lat' => (float) $booking->dropoff_lat, 'lng' => (float) $booking->dropoff_lng, 'title' => $booking->dropoff_title],
            'channel' => Channel::booking((int) $booking->id),
        ];
    }
}
