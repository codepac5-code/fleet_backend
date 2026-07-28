<?php

namespace App\Http\Core\Repositories\Ride;

use App\Models\RideBooking;
use Closure;
use Illuminate\Support\Collection;

interface RideBookingRepository
{
    public function find(int $id): ?RideBooking;

    public function findForUser(int $id, int $userId): ?RideBooking;

    public function findByIdempotencyKey(int $userId, string $key): ?RideBooking;

    public function create(array $attributes): RideBooking;

    public function save(RideBooking $booking): void;

    public function history(int $userId, string $filter, ?int $cursorId, int $limit): Collection;

    public function activeForUser(int $userId): ?RideBooking;

    public function scheduledForUser(int $userId): Collection;

    public function dueScheduled(string $beforeIso, int $limit): Collection;

    public function recentDropoffsForUser(int $userId, int $scan): Collection;

    public function transaction(Closure $callback): mixed;
}
