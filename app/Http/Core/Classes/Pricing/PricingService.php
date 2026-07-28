<?php

namespace App\Http\Core\Classes\Pricing;

class PricingService
{
    public const STYLE_METER = 'meter';
    public const STYLE_FIXED = 'fixed';

    public function quote(array $tariff, float $distanceMeters, int $durationSeconds): array
    {
        $style = $tariff['pricing_style'] ?? self::STYLE_METER;

        if ($style === self::STYLE_FIXED) {
            $fare = max(0, (int) ($tariff['fixed_minor'] ?? 0));

            return [
                'pricing_style' => self::STYLE_FIXED,
                'fare_minor' => $fare,
                'breakdown' => ['fixed' => $fare],
            ];
        }

        $base = (int) ($tariff['base_minor'] ?? 0);
        $perKm = (int) ($tariff['per_km_minor'] ?? 0);
        $perMinute = (int) ($tariff['per_minute_minor'] ?? 0);
        $minimum = (int) ($tariff['minimum_minor'] ?? 0);

        $km = max(0.0, $distanceMeters) / 1000.0;
        $minutes = max(0, $durationSeconds) / 60.0;

        $distancePart = (int) round($perKm * $km);
        $timePart = (int) round($perMinute * $minutes);
        $subtotal = $base + $distancePart + $timePart;
        $fare = max($subtotal, $minimum);

        return [
            'pricing_style' => self::STYLE_METER,
            'fare_minor' => $fare,
            'breakdown' => [
                'base' => $base,
                'distance' => $distancePart,
                'time' => $timePart,
                'subtotal' => $subtotal,
                'minimum_applied' => $fare > $subtotal,
            ],
        ];
    }
}
