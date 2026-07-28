<?php

namespace App\Http\Core\Repositories\Ledger;

interface CommissionSnapshotRepository
{
    public function existsForBooking(int $bookingId): bool;
}
