<?php

namespace App\Http\Core\Classes\Places;

interface GeocodingProvider
{
    public function autocomplete(string $query, ?float $lat, ?float $lng, ?string $session, ?string $country = null): array;

    public function details(string $placeId): ?array;

    public function reverse(float $lat, float $lng): ?array;
}
