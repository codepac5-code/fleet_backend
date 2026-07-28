<?php

namespace App\Http\Core\Classes\Pricing;

use App\Http\Core\Exceptions\DomainException;
use App\Http\Core\GeoServices\RouteEstimator;
use App\Http\Core\GeoServices\ShardManager;
use App\Models\OfficeSubServicePrice;
use App\Models\SubService;

/**
 * Meter pricing for direct-to-driver rides — the OTHER pricing style (fixed
 * corridors are priced separately from travel_routes).
 *
 * A meter fare is `openPrice + kmPrice × km + minutePrice × min`, where the
 * rates come from the sub-service (`sub_services`) unless the dispatching office
 * has published an override (`office_sub_service_prices`). Distance + duration
 * are the real driving estimate ([RouteEstimator]); rates are major-unit
 * decimals, the ledger works in minor units.
 */
class MeterPricingService
{
    public function __construct(private RouteEstimator $routes = new RouteEstimator())
    {
    }

    /**
     * Quote a meter fare from an already-known distance + duration.
     *
     * @return array{fare_minor:int, pricing_style:string, currency_code:string,
     *               distance_m:int, duration_s:int, breakdown:array<string,int>}
     */
    public function quote(int $officeId, int $subServiceId, int $distanceMeters, int $durationSeconds): array
    {
        $sub = SubService::query()->find($subServiceId);
        if ($sub === null) {
            throw DomainException::notFound('sub_service_not_found');
        }

        [$open, $perKm, $perMin] = $this->rates($officeId, $sub);

        $km = max(0, $distanceMeters) / 1000;
        $minutes = max(0, $durationSeconds) / 60;

        $openMinor = $this->minor($open);
        $distanceMinor = (int) round($this->minor($perKm) * $km);
        $timeMinor = (int) round($this->minor($perMin) * $minutes);
        $fareMinor = max(0, $openMinor + $distanceMinor + $timeMinor);

        return [
            'fare_minor' => $fareMinor,
            'pricing_style' => 'meter',
            'currency_code' => ShardManager::currency(),
            'distance_m' => (int) $distanceMeters,
            'duration_s' => (int) $durationSeconds,
            'breakdown' => [
                'open' => $openMinor,
                'distance' => $distanceMinor,
                'time' => $timeMinor,
            ],
        ];
    }

    /**
     * Quote from coordinates — resolves the real driving distance + duration
     * (haversine fallback when Directions is unavailable) then prices it.
     */
    public function quoteRoute(int $officeId, int $subServiceId, float $pLat, float $pLng, float $dLat, float $dLng): array
    {
        [$distance, $duration] = $this->routes->estimate($pLat, $pLng, $dLat, $dLng);

        return $this->quote($officeId, $subServiceId, $distance, $duration);
    }

    /**
     * The effective meter rates [open, perKm, perMinute] in MAJOR units: the
     * office's override when it has one, otherwise the sub-service's base rates.
     *
     * @return array{0: float, 1: float, 2: float}
     */
    private function rates(int $officeId, SubService $sub): array
    {
        $override = $officeId > 0
            ? OfficeSubServicePrice::query()
                ->where('office_id', $officeId)
                ->where('sub_service_id', $sub->id)
                ->first()
            : null;

        $src = $override ?? $sub;

        return [(float) $src->openPrice, (float) $src->kmPrice, (float) $src->minutePrice];
    }

    /** Major decimal → minor units (2 dp). */
    private function minor(float $major): int
    {
        return (int) round($major * 100);
    }
}
