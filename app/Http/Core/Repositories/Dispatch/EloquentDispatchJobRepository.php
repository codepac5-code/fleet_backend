<?php

namespace App\Http\Core\Repositories\Dispatch;

use App\Http\Core\Const\Dispatch\DispatchStatus;
use App\Http\Core\Const\Ride\BookingStatus;
use App\Models\DispatchJob;

class EloquentDispatchJobRepository implements DispatchJobRepository
{
    public function withAssignedDriver(int $bookingId): ?DispatchJob
    {
        return DispatchJob::query()
            ->where('booking_id', $bookingId)
            ->whereNotNull('assigned_driver_id')
            ->first();
    }

    public function activeAssignment(int $bookingId): ?DispatchJob
    {
        return DispatchJob::query()
            ->where('booking_id', $bookingId)
            ->whereNotNull('assigned_driver_id')
            ->where('status', DispatchStatus::ASSIGNED)
            ->first();
    }

    public function assignmentForDriver(int $bookingId, int $driverId): ?DispatchJob
    {
        return DispatchJob::query()
            ->where('booking_id', $bookingId)
            ->where('assigned_driver_id', $driverId)
            ->first();
    }

    public function currentAssignment(int $driverId): ?DispatchJob
    {
        return DispatchJob::query()
            ->where('assigned_driver_id', $driverId)
            ->where('status', DispatchStatus::ASSIGNED)
            ->whereExists(fn ($q) => $q->from('ride_bookings')
                ->whereColumn('ride_bookings.id', 'dispatch_jobs.booking_id')
                ->whereNotIn('ride_bookings.status', BookingStatus::TERMINAL))
            ->orderByDesc('id')
            ->first();
    }
}
