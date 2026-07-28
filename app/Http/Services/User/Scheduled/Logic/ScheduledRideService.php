<?php

namespace App\Http\Services\User\Scheduled\Logic;

use App\Http\Core\Classes\Ride\ScheduledService;
use App\Http\Core\Const\Ride\BookingStatus;
use App\Http\Core\Repositories\Ride\RideBookingRepository;
use App\Http\Services\User\Support\Presenters\BookingPresenter;
use App\Http\Services\User\Support\Presenters\OfficePresenter;
use App\Models\Office;
use App\Models\RideBooking;

class ScheduledRideService
{
    public function __construct(
        private ScheduledService $scheduled,
        private RideBookingRepository $bookings
    ) {
    }

    public function create(int $userId, array $v): array
    {
        $route = $v['route'];

        $in = [
            'office_id' => (int) $v['office_id'],
            'service' => (string) $route['service'],
            'service_class' => (string) $route['serviceClass'],
            // Present → meter pricing (open+km+min from the sub-service).
            'sub_service_id' => isset($v['sub_service_id']) ? (int) $v['sub_service_id'] : null,
            'scheduled_at' => $v['scheduledFor'],
            'pickup' => [
                'lat' => (float) $route['pickup']['lat'],
                'lng' => (float) $route['pickup']['lng'],
                'title' => $route['pickup']['title'] ?? null,
            ],
            'dropoff' => [
                'lat' => (float) $route['dropoff']['lat'],
                'lng' => (float) $route['dropoff']['lng'],
                'title' => $route['dropoff']['title'] ?? null,
            ],
            'passengers' => $v['passengers'] ?? null,
            'luggage' => $v['luggage'] ?? null,
            'flight_no' => $v['flightNo'] ?? null,
        ];

        $result = $this->scheduled->create($userId, $in);

        return $this->present((int) $result['booking_id']);
    }

    public function show(int $userId, int $bookingId): array
    {
        $this->scheduled->show($userId, $bookingId);

        return $this->present($bookingId);
    }

    public function update(int $userId, int $bookingId, array $v): array
    {
        $attrs = [];

        if (array_key_exists('scheduledFor', $v) && $v['scheduledFor'] !== null) {
            $attrs['scheduled_at'] = $v['scheduledFor'];
        }
        if (array_key_exists('passengers', $v)) {
            $attrs['passengers'] = $v['passengers'];
        }
        if (array_key_exists('luggage', $v)) {
            $attrs['luggage'] = $v['luggage'];
        }
        if (array_key_exists('flightNo', $v)) {
            $attrs['flight_no'] = $v['flightNo'];
        }

        $result = $this->scheduled->update($userId, $bookingId, $attrs);

        $booking = $this->bookings->findForUser($bookingId, $userId);
        $booking->change_revision = (int) $booking->change_revision + 1;
        $this->bookings->save($booking);

        return $this->present((int) $result['booking_id']);
    }

    public function cancel(int $userId, int $bookingId): void
    {
        $this->scheduled->cancel($userId, $bookingId);
    }

    private function present(int $bookingId): array
    {
        $booking = $this->bookings->find($bookingId);
        $office = Office::query()->find((int) $booking->office_id);

        return array_merge(
            BookingPresenter::row($booking),
            [
                // A scheduled trip is meter (direct-to-driver) unless it was priced
                // as a fixed corridor — the rider timeline branches on this.
                'trip_type' => $booking->pricing_style === 'fixed' ? 'fixed' : 'meter',
                'office' => $office !== null ? OfficePresenter::card($office) : null,
                'steps' => $this->steps($booking),
            ]
        );
    }

    private function steps(RideBooking $booking): array
    {
        $status = (string) $booking->status;
        $done = fn (bool $ok) => $ok ? 'done' : 'pending';

        $isCancelled = in_array($status, [BookingStatus::CANCELLED, BookingStatus::REJECTED], true);
        $isMatching = ! $isCancelled && $status !== BookingStatus::SCHEDULED;
        $isAssigned = $booking->driver_id !== null || $booking->assigned_at !== null;
        $isCompleted = $status === BookingStatus::COMPLETED;

        return [
            ['key' => 'scheduled', 'label' => 'Scheduled', 'status' => 'done', 'at' => $this->iso($booking->created_at)],
            ['key' => 'matching', 'label' => 'Finding a driver', 'status' => $isMatching ? ($isAssigned ? 'done' : 'current') : $done(false), 'at' => null],
            ['key' => 'assigned', 'label' => 'Driver assigned', 'status' => $done($isAssigned), 'at' => $this->iso($booking->assigned_at)],
            ['key' => 'completed', 'label' => 'Completed', 'status' => $done($isCompleted), 'at' => $this->iso($booking->completed_at)],
        ];
    }

    private function iso($dt): ?string
    {
        return $dt !== null ? $dt->toIso8601ZuluString() : null;
    }
}
