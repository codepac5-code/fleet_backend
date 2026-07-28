<?php

namespace App\Http\Services\User\Booking\Logic;

use App\Http\Core\Classes\Ride\RideBookingService;
use App\Http\Core\Repositories\Ride\RideBookingRepository;
use App\Http\Services\User\Support\Presenters\BookingPresenter;
use App\Http\Services\User\Support\Presenters\OfficePresenter;
use App\Models\Office;
use App\Models\RideBooking;
use App\Models\RideRating;

class BookingService
{
    public function __construct(
        private RideBookingService $rides,
        private RideBookingRepository $bookings
    ) {
    }

    public function create(int $userId, array $v, string $idempotencyKey): array
    {
        $in = [
            'office_id' => (int) $v['office_id'],
            'service' => $v['service'] ?? 'ride',
            'service_class' => (string) $v['service_class'],
            'pickup' => [
                'lat' => (float) $v['pickup_lat'],
                'lng' => (float) $v['pickup_lng'],
                'title' => $v['pickup_title'] ?? null,
                'note' => $v['pickup_note'] ?? null,
            ],
            'dropoff' => [
                'lat' => (float) $v['dropoff_lat'],
                'lng' => (float) $v['dropoff_lng'],
                'title' => $v['dropoff_title'] ?? null,
            ],
            'stops' => $v['stops'] ?? null,
            'distance_m' => (int) ($v['distance_m'] ?? 0),
            'duration_s' => (int) ($v['duration_s'] ?? 0),
            'payment_method' => $v['payment_method'] ?? 'wallet',
            'promo_code' => $v['promo_code'] ?? null,
        ];
        // NOTE: `scheduled_at` is deliberately NOT forwarded. CreateBookingRequest
        // accepts it, but RideBookingService dispatches immediately and has no
        // notion of a future pickup, so passing it through would look like
        // support without being it. Scheduled rides go to POST /scheduled
        // (ScheduledRideService), which does honour the time.

        $result = $this->rides->create($userId, $in, $idempotencyKey);
        $booking = $this->bookings->find((int) $result['booking_id']);

        return BookingPresenter::row($booking, (string) $result['status']);
    }

    public function cancel(int $userId, int $bookingId, ?string $reason): array
    {
        $result = $this->rides->cancel($userId, $bookingId, $reason);
        $booking = $this->bookings->find((int) $result['booking_id']);

        return BookingPresenter::row($booking, (string) $result['status']);
    }

    /**
     * Move a still-matching booking to another office. The core service repricess
     * against the new office's tariff, reconciles escrow and re-runs dispatch;
     * we re-present the booking through `detail()` so the client gets the very
     * same shape as `GET user/bookings/{id}` — new price, new office card and the
     * (now cleared) driver — and can refresh its Booking model with one parser.
     */
    public function changeOffice(int $userId, int $bookingId, int $officeId): array
    {
        $this->rides->changeOffice($userId, $bookingId, $officeId);

        return $this->detail($userId, $bookingId);
    }

    public function detail(int $userId, int $bookingId): array
    {
        $result = $this->rides->get($userId, $bookingId);
        $booking = $this->bookings->findForUser($bookingId, $userId);

        $office = Office::query()->find((int) $booking->office_id);

        $rating = RideRating::query()
            ->where('booking_id', $bookingId)
            ->where('rater_type', 'user')
            ->first();

        return BookingPresenter::detail(
            $booking,
            (string) $result['status'],
            $office !== null ? OfficePresenter::card($office) : null,
            $rating !== null ? BookingPresenter::rating($rating) : null,
            $result['driver'] ?? null
        );
    }

    public function bookingFor(int $userId, int $bookingId): ?RideBooking
    {
        return $this->bookings->findForUser($bookingId, $userId);
    }
}
