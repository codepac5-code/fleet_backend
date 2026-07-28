<?php

namespace App\Http\Core\Classes\Places;

class NullGeocodingProvider implements GeocodingProvider
{
    public function autocomplete(string $query, ?float $lat, ?float $lng, ?string $session, ?string $country = null): array
    {
        return [];
    }

    public function details(string $placeId): ?array
    {
        return null;
    }

    public function reverse(float $lat, float $lng): ?array
    {
        return null;
    }
}
