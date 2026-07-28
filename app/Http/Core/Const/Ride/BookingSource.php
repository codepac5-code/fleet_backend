<?php

namespace App\Http\Core\Const\Ride;

class BookingSource
{
    const APP = 'app';
    const RIDER = 'rider';
    const DRIVER = 'driver';
    const OFFICE = 'office';
    const SYSTEM = 'system';

    const ALL = [self::APP, self::RIDER, self::DRIVER, self::OFFICE, self::SYSTEM];

    public static function isValid(string $source): bool
    {
        return in_array($source, self::ALL, true);
    }
}
