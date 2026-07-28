<?php

namespace App\Http\Core\Repositories\Ledger;

use Illuminate\Support\Collection;

interface DriverStatementRepository
{
    public function earnings(int $driverId, string $currency, ?string $sinceIso): array;

    public function completedTrips(int $driverId, ?int $cursorId, int $limit): Collection;

    public function tripDetail(int $driverId, int $bookingId): ?object;

    public function offerCounts(int $driverId): array;
}
