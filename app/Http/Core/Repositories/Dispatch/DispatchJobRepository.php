<?php

namespace App\Http\Core\Repositories\Dispatch;

use App\Models\DispatchJob;

interface DispatchJobRepository
{
    public function withAssignedDriver(int $bookingId): ?DispatchJob;

    public function activeAssignment(int $bookingId): ?DispatchJob;

    public function assignmentForDriver(int $bookingId, int $driverId): ?DispatchJob;

    public function currentAssignment(int $driverId): ?DispatchJob;
}
