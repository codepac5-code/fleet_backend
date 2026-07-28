<?php

namespace App\Http\Core\Classes\Dispatch;

class Geo
{
    const EARTH_RADIUS_M = 6371000;

    public static function haversineMeters(float $lat1, float $lng1, float $lat2, float $lng2): int
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return (int) round(self::EARTH_RADIUS_M * $c);
    }

    public static function boundingBox(float $lat, float $lng, float $radiusMeters): array
    {
        $latDelta = rad2deg($radiusMeters / self::EARTH_RADIUS_M);
        $cosLat = cos(deg2rad($lat));
        $lngDelta = $cosLat > 0.000001
            ? rad2deg($radiusMeters / (self::EARTH_RADIUS_M * $cosLat))
            : 180.0;

        return [
            'lat_min' => $lat - $latDelta,
            'lat_max' => $lat + $latDelta,
            'lng_min' => $lng - $lngDelta,
            'lng_max' => $lng + $lngDelta,
        ];
    }
}
