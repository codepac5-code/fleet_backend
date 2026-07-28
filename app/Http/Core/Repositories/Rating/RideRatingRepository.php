<?php

namespace App\Http\Core\Repositories\Rating;

use App\Models\RideRating;
use Illuminate\Support\Collection;

interface RideRatingRepository
{
    public function firstOrCreate(array $keys, array $values): RideRating;

    public function aggregateFor(string $rateeType, int $rateeId): array;

    public function forBooking(int $bookingId): Collection;

    public function feedAll(?string $rateeType, ?int $maxStars, int $limit): Collection;

    public function feedForOfficeScope(int $officeId, array $driverIds, ?int $maxStars, int $limit): Collection;
}
