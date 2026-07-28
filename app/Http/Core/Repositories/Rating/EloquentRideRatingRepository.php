<?php

namespace App\Http\Core\Repositories\Rating;

use App\Models\RideRating;
use Illuminate\Support\Collection;

class EloquentRideRatingRepository implements RideRatingRepository
{
    public function firstOrCreate(array $keys, array $values): RideRating
    {
        return RideRating::query()->firstOrCreate($keys, $values);
    }

    public function aggregateFor(string $rateeType, int $rateeId): array
    {
        $query = RideRating::query()->where('ratee_type', $rateeType)->where('ratee_id', $rateeId);
        $count = (int) $query->count();
        $average = $count > 0 ? round((float) $query->avg('stars'), 2) : 0.0;

        return ['count' => $count, 'average' => $average];
    }

    public function forBooking(int $bookingId): Collection
    {
        return RideRating::query()->where('booking_id', $bookingId)->orderBy('id')->get();
    }

    public function feedAll(?string $rateeType, ?int $maxStars, int $limit): Collection
    {
        $query = RideRating::query()->orderByDesc('id');

        if ($rateeType !== null && $rateeType !== '') {
            $query->where('ratee_type', $rateeType);
        }

        if ($maxStars !== null) {
            $query->where('stars', '<=', $maxStars);
        }

        return $query->limit($limit)->get();
    }

    public function feedForOfficeScope(int $officeId, array $driverIds, ?int $maxStars, int $limit): Collection
    {
        $query = RideRating::query()
            ->where(function ($w) use ($officeId, $driverIds) {
                $w->where(fn ($o) => $o->where('ratee_type', 'office')->where('ratee_id', $officeId));

                if ($driverIds !== []) {
                    $w->orWhere(fn ($d) => $d->where('ratee_type', 'driver')->whereIn('ratee_id', $driverIds));
                }
            })
            ->orderByDesc('id');

        if ($maxStars !== null) {
            $query->where('stars', '<=', $maxStars);
        }

        return $query->limit($limit)->get();
    }
}
