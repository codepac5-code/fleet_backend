<?php

namespace App\Http\Core\Classes\Rating;

use App\Http\Core\Classes\Event\DomainEvent;
use App\Http\Core\Classes\Event\EventBus;
use App\Http\Core\Const\Event\Channel;
use App\Http\Core\Const\Event\EventType;
use App\Http\Core\Exceptions\DomainException;
use App\Http\Core\Repositories\Rating\RideRatingRepository;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\Driver;
use App\Models\RideRating;
use Throwable;

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
            $channels = [match ($rateeType) {
                'user' => Channel::user($rateeId),
                'office' => Channel::office($rateeId),
                default => Channel::driver($rateeId),
            }];

            // A rating went ONLY to whoever was rated, so a one-star driver was
            // known to that driver alone — the office responsible for them and
            // the fleet quality desk both heard nothing.
            if ($rateeType === 'driver') {
                // Widening the audience must never be able to fail the rating
                // itself, so the lookup degrades to "driver only".
                try {
                    $officeId = (int) (Driver::on(TenantConnection::current())->whereKey($rateeId)->value('officeId') ?? 0);
                } catch (Throwable $e) {
                    $officeId = 0;
                }

                if ($officeId > 0) {
                    $channels[] = Channel::office($officeId);
                }
            }

            if ($rateeType !== 'user') {
                $channels[] = Channel::admin();
            }

            $this->events->emit(new DomainEvent(
                EventType::RATING_RECEIVED,
                $channels,
                [
                    'booking_id' => $bookingId,
                    'stars' => $stars,
                    'from_role' => $raterType,
                    'ratee_type' => $rateeType,
                    'ratee_id' => $rateeId,
                ]
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
