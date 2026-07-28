<?php

namespace App\Http\Core\Repositories\Ride;

use App\Http\Core\Const\Ride\BookingStatus;
use App\Models\RideBooking;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EloquentRideBookingRepository implements RideBookingRepository
{
    public function find(int $id): ?RideBooking
    {
        return RideBooking::query()->find($id);
    }

    public function findForUser(int $id, int $userId): ?RideBooking
    {
        return RideBooking::query()->where('id', $id)->where('user_id', $userId)->first();
    }

    public function findByIdempotencyKey(int $userId, string $key): ?RideBooking
    {
        return RideBooking::query()
            ->where('user_id', $userId)
            ->where('idempotency_key', $key)
            ->first();
    }

    public function create(array $attributes): RideBooking
    {
        return RideBooking::query()->create($attributes);
    }

    public function save(RideBooking $booking): void
    {
        $booking->save();
    }

    public function history(int $userId, string $filter, ?int $cursorId, int $limit): Collection
    {
        $query = RideBooking::query()->where('user_id', $userId);
        $this->applyFilter($query, $filter);

        if ($cursorId !== null) {
            $query->where('id', '<', $cursorId);
        }

        return $query->orderByDesc('id')->limit($limit + 1)->get();
    }

    public function activeForUser(int $userId): ?RideBooking
    {
        return RideBooking::query()
            ->where('user_id', $userId)
            ->whereIn('status', array_merge([BookingStatus::MATCHING, BookingStatus::ASSIGNED], BookingStatus::LIVE_SUB))
            ->whereNotExists(fn ($s) => $s->from('commission_snapshots')->whereColumn('commission_snapshots.booking_id', 'ride_bookings.id'))
            ->orderByDesc('id')
            ->first();
    }

    public function scheduledForUser(int $userId): Collection
    {
        return RideBooking::query()
            ->where('user_id', $userId)
            ->where('status', BookingStatus::SCHEDULED)
            ->orderBy('scheduled_at')
            ->get();
    }

    public function dueScheduled(string $beforeIso, int $limit): Collection
    {
        return RideBooking::query()
            ->where('status', BookingStatus::SCHEDULED)
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', $beforeIso)
            ->orderBy('scheduled_at')
            ->limit($limit)
            ->get();
    }

    public function recentDropoffsForUser(int $userId, int $scan): Collection
    {
        return RideBooking::query()
            ->where('user_id', $userId)
            ->orderByDesc('id')
            ->limit($scan)
            ->get(['dropoff_lat', 'dropoff_lng', 'dropoff_title', 'created_at']);
    }

    public function transaction(Closure $callback): mixed
    {
        return DB::connection((new RideBooking)->getConnectionName())->transaction($callback);
    }

    private function applyFilter(Builder $query, string $filter): void
    {
        if ($filter === 'completed') {
            $query->where(fn ($w) => $w->where('status', BookingStatus::COMPLETED)
                ->orWhereExists(fn ($s) => $s->from('commission_snapshots')->whereColumn('commission_snapshots.booking_id', 'ride_bookings.id')));

            return;
        }

        if ($filter === 'cancelled') {
            $query->whereIn('status', [BookingStatus::CANCELLED, BookingStatus::REJECTED]);

            return;
        }

        // SCHEDULED belongs here: it matched none of the three filters, so a
        // booked-ahead ride was invisible in every history tab — the rider
        // scheduled a trip and then could not find it anywhere.
        $query->whereIn('status', array_merge(
            [BookingStatus::SCHEDULED, BookingStatus::MATCHING, BookingStatus::ASSIGNED],
            BookingStatus::LIVE_SUB,
        ))
            ->whereNotExists(fn ($s) => $s->from('commission_snapshots')->whereColumn('commission_snapshots.booking_id', 'ride_bookings.id'));
    }
}
