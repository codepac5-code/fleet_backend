<?php

namespace App\Http\Core\Classes\Rating;

use App\Http\Core\Classes\Marketplace\FavoriteOfficeService;
use App\Http\Core\Exceptions\DomainException;
use App\Http\Core\Repositories\Dispatch\DispatchJobRepository;
use App\Http\Core\Repositories\Ride\RideBookingRepository;
use App\Models\RideRating;

class RiderRatingService
{
    public function __construct(
        private RatingService $ratings,
        private RideBookingRepository $bookings,
        private DispatchJobRepository $jobs,
        private FavoriteOfficeService $favorites
    ) {
    }

    public function rateOffice(int $userId, int $bookingId, int $stars, ?string $comment, bool $favorite): array
    {
        $booking = $this->bookings->findForUser($bookingId, $userId);

        if ($booking === null) {
            throw DomainException::notFound();
        }

        $officeId = (int) $booking->office_id;
        $rating = $this->ratings->rate($bookingId, 'user', $userId, 'office', $officeId, $stars, $comment);

        if ($favorite) {
            $this->favorites->add($userId, $officeId);
        }

        return $this->present($bookingId, $rating);
    }

    public function rateDriver(int $userId, int $bookingId, int $stars, ?string $comment): array
    {
        $booking = $this->bookings->findForUser($bookingId, $userId);

        if ($booking === null) {
            throw DomainException::notFound();
        }

        $job = $this->jobs->withAssignedDriver($bookingId);

        if ($job === null) {
            throw DomainException::make('ride_not_rateable', 422, 'No assigned driver for this booking.');
        }

        $rating = $this->ratings->rate($bookingId, 'user', $userId, 'driver', (int) $job->assigned_driver_id, $stars, $comment);

        return $this->present($bookingId, $rating);
    }

    public function rateRider(int $driverId, int $bookingId, int $stars, ?string $comment): array
    {
        $job = $this->jobs->assignmentForDriver($bookingId, $driverId);

        if ($job === null) {
            throw DomainException::make('ride_not_rateable', 422, 'This ride is not assigned to you.');
        }

        $booking = $this->bookings->find($bookingId);

        if ($booking === null) {
            throw DomainException::notFound();
        }

        $rating = $this->ratings->rate($bookingId, 'driver', $driverId, 'user', (int) $booking->user_id, $stars, $comment);

        return $this->present($bookingId, $rating);
    }

    public function driverSummary(int $driverId): array
    {
        return $this->ratings->summaryFor('driver', $driverId);
    }

    private function present(int $bookingId, RideRating $rating): array
    {
        return [
            'booking_id' => $bookingId,
            'ratee_type' => $rating->ratee_type,
            'ratee_id' => (int) $rating->ratee_id,
            'stars' => (int) $rating->stars,
        ];
    }
}
