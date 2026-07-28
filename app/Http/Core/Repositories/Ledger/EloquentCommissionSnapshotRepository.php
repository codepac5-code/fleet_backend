<?php

namespace App\Http\Core\Repositories\Ledger;

use App\Models\CommissionSnapshot;

class EloquentCommissionSnapshotRepository implements CommissionSnapshotRepository
{
    public function existsForBooking(int $bookingId): bool
    {
        return CommissionSnapshot::query()->where('booking_id', $bookingId)->exists();
    }
}
