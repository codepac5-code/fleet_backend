<?php

namespace App\Http\Core\Classes\Ride;

class Polyline
{
    public static function encode(array $points): string
    {
        $result = '';
        $prevLat = 0;
        $prevLng = 0;

        foreach ($points as $point) {
            $lat = (int) round($point[0] * 1e5);
            $lng = (int) round($point[1] * 1e5);

            $result .= self::chunk($lat - $prevLat);
            $result .= self::chunk($lng - $prevLng);

            $prevLat = $lat;
            $prevLng = $lng;
        }

        return $result;
    }

    private static function chunk(int $value): string
    {
        $value = $value < 0 ? ~($value << 1) : ($value << 1);
        $chunk = '';

        while ($value >= 0x20) {
            $chunk .= chr((0x20 | ($value & 0x1f)) + 63);
            $value >>= 5;
        }

        $chunk .= chr($value + 63);

        return $chunk;
    }
}
