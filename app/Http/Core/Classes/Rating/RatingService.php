<?php

namespace App\Http\Core\Classes\Rating;

use App\Http\Core\Classes\Event\DomainEvent;
use App\Http\Core\Classes\Event\EventBus;
use App\Http\Core\Const\Event\Channel;
use App\Http\Core\Const\Event\EventType;
use App\Http\Core\Exceptions\DomainException;
use App\Http\Core\Repositories\Rating\RideRatingRepository;
use App\Models\RideRating;

class RatingService
{
    public function __construct(
        private RideRatingRepository $repository,
        private ?EventBus $events = null
    ) {
    }

    public function rate(int $bookingId, string $raterType, int $raterId, string $rateeType, int $rateeId, int $stars, ?string $comment = null): RideRating
    {
        if ($stars < 1 || $stars > 5) {
            throw DomainException::make('validation_failed', 422, 'stars must be between 1 and 5.');
        }

        if ($raterType === $rateeType && $raterId === $rateeId) {
            throw DomainException::make('validation_failed', 422, 'cannot rate yourself.');
        }

        $rating = $this->repository->firstOrCreate(
            ['booking_id' => $bookingId, 'rater_type' => $raterType, 'ratee_type' => $rateeType],
            [
                'rater_id' => $raterId,
                'ratee_id' => $rateeId,
                'stars' => $stars,
                'comment' => $comment,
                'created_at' => now(),
            ]
        );

        if ($this->events !== null && $rating->wasRecentlyCreated) {
            $channel = match ($rateeType) {
                'user' => Channel::user($rateeId),
                'office' => Channel::office($rateeId),
                default => Channel::driver($rateeId),
            };

            $this->events->emit(new DomainEvent(
                EventType::RATING_RECEIVED,
                [$channel],
                ['booking_id' => $bookingId, 'stars' => $stars, 'from_role' => $raterType]
            ));
        }

        return $rating;
    }

    public function summaryFor(string $rateeType, int $rateeId): array
    {
        $aggregate = $this->repository->aggregateFor($rateeType, $rateeId);

        return [
            'ratee_type' => $rateeType,
            'ratee_id' => $rateeId,
            'count' => $aggregate['count'],
            'average' => $aggregate['average'],
        ];
    }

    public function forBooking(int $bookingId): array
    {
        return $this->repository->forBooking($bookingId)->all();
    }
}
