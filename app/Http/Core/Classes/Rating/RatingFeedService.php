<?php

namespace App\Http\Core\Classes\Rating;

use App\Http\Core\Repositories\Driver\DriverDirectoryRepository;
use App\Http\Core\Repositories\Rating\RideRatingRepository;
use App\Models\RideRating;

class RatingFeedService
{
    public function __construct(
        private RideRatingRepository $ratings,
        private DriverDirectoryRepository $drivers
    ) {
    }

    public function adminFeed(?string $rateeType, ?int $maxStars, int $limit): array
    {
        return $this->ratings->feedAll($rateeType, $maxStars, $this->cap($limit))
            ->map(fn (RideRating $r) => $this->present($r))
            ->all();
    }

    public function officeFeed(int $officeId, ?int $maxStars, int $limit): array
    {
        $driverIds = $this->drivers->idsForOffice($officeId);

        return $this->ratings->feedForOfficeScope($officeId, $driverIds, $maxStars, $this->cap($limit))
            ->map(fn (RideRating $r) => $this->present($r))
            ->all();
    }

    private function cap(int $limit): int
    {
        return min(max($limit, 1), 100);
    }

    private function present(RideRating $rating): array
    {
        return [
            'id' => (int) $rating->id,
            'booking_id' => (int) $rating->booking_id,
            'rater_type' => $rating->rater_type,
            'ratee_type' => $rating->ratee_type,
            'ratee_id' => (int) $rating->ratee_id,
            'stars' => (int) $rating->stars,
            'comment' => $rating->comment,
            'tags' => is_array($rating->tags) ? $rating->tags : [],
            'book_again' => $rating->book_again,
            'favorite' => $rating->favorite,
            'at' => optional($rating->created_at)->toIso8601String(),
        ];
    }
}
