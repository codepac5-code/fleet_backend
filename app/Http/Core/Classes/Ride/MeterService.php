<?php

namespace App\Http\Core\Classes\Ride;

use App\Http\Core\Classes\Pricing\PricingService;
use App\Http\Core\Classes\Pricing\TariffResolver;
use App\Http\Core\Const\Ride\BookingStatus;
use App\Models\RideBooking;

/**
 * The live trip meter, accumulated SERVER-SIDE. Each driver GPS ping during an
 * ON_TRIP meter ride adds the ping-to-ping distance to the running total and
 * recomputes the running fare (open + per-km + per-minute), so the figure the
 * rider and driver watch tick is authoritative — a client can't inflate it.
 *
 * It mutates the booking's meter fields but does NOT persist; the caller saves.
 * The final billed fare is still capped at the expected quote at settlement
 * (see DriverTripService::reconcileFinalFare) — this is the live estimate.
 */
class MeterService
{
    public function __construct(
        private TariffResolver $tariffs,
        private PricingService $pricing
    ) {
    }

    /**
     * Fold one GPS ping into the meter. Returns the live snapshot to broadcast,
     * or null when the booking isn't a running metered trip.
     *
     * @return array{booking_id:int, distance_m:int, duration_s:int, fare_minor:int, currency_code:string, at:string}|null
     */
    public function tick(RideBooking $booking, float $lat, float $lng): ?array
    {
        if ($booking->status !== BookingStatus::ON_TRIP || $booking->pricing_style !== 'meter') {
            return null;
        }

        if ($booking->meter_last_lat === null || $booking->meter_last_lng === null) {
            // First ping: anchor the start point (and the clock, if start didn't).
            $booking->meter_last_lat = $lat;
            $booking->meter_last_lng = $lng;
            if ($booking->trip_started_at === null) {
                $booking->trip_started_at = now();
            }
        } else {
            $delta = $this->haversineMeters((float) $booking->meter_last_lat, (float) $booking->meter_last_lng, $lat, $lng);
            $booking->meter_distance_m = (int) $booking->meter_distance_m + (int) round($delta);
            $booking->meter_last_lat = $lat;
            $booking->meter_last_lng = $lng;
        }

        $durationS = $booking->trip_started_at
            ? max(0, now()->getTimestamp() - $booking->trip_started_at->getTimestamp())
            : 0;

        $fareMinor = $this->runningFare($booking, (int) $booking->meter_distance_m, $durationS);

        // SUPERSET payload so both clients consume it unchanged: the rider app
        // reads `elapsed_s` + `running_fare` (major units); the driver app reads
        // `duration_s` + `total_minor` (minor units). `distance_m` is shared.
        return [
            'booking_id' => (int) $booking->id,
            'distance_m' => (int) $booking->meter_distance_m,
            'elapsed_s' => $durationS,
            'duration_s' => $durationS,
            'running_fare' => round($fareMinor / 100, 2),
            'fare_minor' => $fareMinor,
            'total_minor' => $fareMinor,
            'currency_code' => (string) $booking->currency_code,
            'at' => now()->toIso8601ZuluString(),
        ];
    }

    private function runningFare(RideBooking $booking, int $distanceM, int $durationS): int
    {
        $tariff = $this->tariffs->forOfficeServiceOrSub((int) $booking->office_id, (int) $booking->sub_service_id ?: null, $booking->service, $booking->service_class);

        if ($tariff === null) {
            return 0;
        }

        return (int) $this->pricing->quote($tariff, $distanceM, $durationS)['fare_minor'];
    }

    /** Great-circle distance between two points, in metres. */
    private function haversineMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earth = 6371000.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $earth * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
