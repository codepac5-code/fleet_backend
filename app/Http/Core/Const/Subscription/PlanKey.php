<?php

namespace App\Http\Core\Const\Subscription;

class PlanKey
{
    const FREE = 'free';
    const STARTER = 'starter';
    const BUSINESS = 'business';
    const SCALE = 'scale';
    const ENTERPRISE = 'enterprise';

    const CATALOG = [
        self::FREE => ['name' => 'Free', 'price_minor' => 0, 'fleet_rate' => 18.0, 'driver_limit' => 5, 'sort' => 1],
        self::STARTER => ['name' => 'Starter', 'price_minor' => 20000, 'fleet_rate' => 13.0, 'driver_limit' => 25, 'sort' => 2],
        self::BUSINESS => ['name' => 'Business', 'price_minor' => 35000, 'fleet_rate' => 12.0, 'driver_limit' => 50, 'sort' => 3],
        self::SCALE => ['name' => 'Scale', 'price_minor' => 50000, 'fleet_rate' => 11.0, 'driver_limit' => 150, 'sort' => 4],
        self::ENTERPRISE => ['name' => 'Enterprise', 'price_minor' => null, 'fleet_rate' => null, 'driver_limit' => null, 'sort' => 5],
    ];

    const DEFAULT_OFFICE_RATE = 0.0;

    public static function exists(string $key): bool
    {
        return array_key_exists($key, self::CATALOG);
    }

    public static function plan(string $key): array
    {
        if (!self::exists($key)) {
            throw new \RuntimeException('unknown subscription plan: ' . $key);
        }

        return self::CATALOG[$key];
    }

    public static function fleetRate(string $key): ?float
    {
        return self::plan($key)['fleet_rate'];
    }
}
