<?php

namespace App\Http\Core\Classes\Ride;

use App\Http\Core\Exceptions\DomainException;
use App\Http\Core\Repositories\Ledger\DriverStatementRepository;
use Illuminate\Support\Carbon;

class DriverTripHistoryService
{
    public function __construct(private DriverStatementRepository $statement)
    {
    }

    public function history(int $driverId, ?int $cursorId, int $limit): array
    {
        $rows = $this->statement->completedTrips($driverId, $cursorId, $limit);
        $hasMore = $rows->count() > $limit;
        $items = $rows->take($limit);

        return [
            'data' => $items->map(fn ($r) => [
                'booking_id' => (int) $r->booking_id,
                'from' => $r->pickup_title,
                'to' => $r->dropoff_title,
                'service' => $r->service,
                'service_class' => $r->service_class,
                'payment_method' => $r->payment_method,
                'pricing_style' => $r->pricing_style,
                'total_minor' => (int) $r->total_minor,
                'earned_minor' => (int) $r->driver_minor,
                'currency_code' => $r->currency_code,
                'at' => optional(Carbon::parse($r->created_at))->toIso8601String(),
            ])->values()->all(),
            'meta' => [
                'next_cursor' => $hasMore ? (string) $items->last()->snapshot_id : null,
                'has_more' => $hasMore,
            ],
        ];
    }

    public function detail(int $driverId, int $bookingId): array
    {
        $r = $this->statement->tripDetail($driverId, $bookingId);

        if ($r === null) {
            throw DomainException::notFound();
        }

        return [
            'booking_id' => (int) $r->booking_id,
            'from' => $r->pickup_title,
            'to' => $r->dropoff_title,
            'service' => $r->service,
            'service_class' => $r->service_class,
            'pricing_style' => $r->pricing_style,
            'payment_method' => $r->payment_method,
            'distance_m' => (int) $r->distance_m,
            'duration_s' => (int) $r->duration_s,
            'office_id' => (int) $r->office_id,
            'currency_code' => $r->currency_code,
            'fare' => [
                'total_minor' => (int) $r->total_minor,
                'fare_minor' => (int) $r->fare_minor,
                'discount_minor' => (int) $r->discount_minor,
                'earned_minor' => (int) $r->driver_minor,
                'fees_minor' => (int) $r->fleet_minor + (int) $r->office_minor,
            ],
            'at' => optional(Carbon::parse($r->created_at))->toIso8601String(),
        ];
    }
}
