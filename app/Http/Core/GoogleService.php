<?php

namespace App\Http\Core;
use Illuminate\Support\Facades\Http;

class GoogleService
{
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = env('GOOGLE_MAPS_KEY');
    }

    public function resolveLocation($lat, $lng): ?array
    {
        $response = Http::get(
            'https://maps.googleapis.com/maps/api/geocode/json',
            [
                'latlng' => "$lat,$lng",
                'key' => $this->apiKey
            ]
        )->json();

        $components = $response['results'][0]['address_components'] ?? null;

        if (!$components) {
            return null;
        }

        $country = null;
        $city = null;

        foreach ($components as $component) {

            if (in_array('country', $component['types'])) {
                $country = $component['short_name'];
            }

            if (
                in_array('locality', $component['types']) ||
                in_array('administrative_area_level_1', $component['types'])
            ) {
                $city = $component['long_name'];
            }
        }

        return [
            'country_code' => $country,
            'city' => $city,
        ];
    }
}
