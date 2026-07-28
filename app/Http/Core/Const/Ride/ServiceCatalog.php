<?php

namespace App\Http\Core\Const\Ride;

class ServiceCatalog
{
    const RIDE = 'ride';
    const PREMIUM = 'premium';
    const TRAVEL = 'travel';

    const CLASSES = [
        self::RIDE => ['standard', 'comfort', 'electric', 'suv', 'van', 'luxury'],
        self::PREMIUM => ['exotic', 'ultra_luxury_sedan', 'ultra_luxury_suv'],
        self::TRAVEL => ['standard', 'comfort', 'electric', 'suv', 'van', 'luxury', 'ultra_luxury'],
    ];

    const STYLE = [
        self::RIDE => 'meter',
        self::PREMIUM => 'meter',
        self::TRAVEL => 'fixed',
    ];

    public static function isService(string $service): bool
    {
        return isset(self::CLASSES[$service]);
    }

    public static function classes(string $service): array
    {
        return self::CLASSES[$service] ?? [];
    }

    public static function style(string $service): string
    {
        return self::STYLE[$service] ?? 'meter';
    }
}
