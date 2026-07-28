<?php

namespace App\Http\Core\Classes\Ride;

use App\Http\Core\Classes\Dispatch\Geo;

class RouteClassifier
{
    private const AIRPORT_RADIUS_M = 3000;
    private const INTERCITY_M = 60000;

    public function classify(float $pickupLat, float $pickupLng, float $dropLat, float $dropLng, int $distanceMeters): string
    {
        foreach ($this->airports() as $airport) {
            $nearPickup = Geo::haversineMeters($pickupLat, $pickupLng, $airport[0], $airport[1]) <= self::AIRPORT_RADIUS_M;
            $nearDrop = Geo::haversineMeters($dropLat, $dropLng, $airport[0], $airport[1]) <= self::AIRPORT_RADIUS_M;

            if ($nearPickup || $nearDrop) {
                return 'airport';
            }
        }

        if ($distanceMeters >= self::INTERCITY_M) {
            return 'intercity';
        }

        return 'local';
    }

    private function airports(): array
    {
        $configured = config('fleet.airports');

        if (is_array($configured) && $configured !== []) {
            return $configured;
        }

        return [
            [25.2731, 51.6080],
        ];
    }
}
