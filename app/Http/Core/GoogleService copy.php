<?php

namespace App\Http\Core;
use Illuminate\Support\Facades\Http;

class GoogleService
{
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey =  env('GOOGLE_MAPS_KEY');
    }

    protected function reverseGeocode($lat, $lng)
    {
        $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
            'latlng' => $lat . ',' . $lng,
            'key' => $this->apiKey
        ])->json();

        return $response['results'][0]['address_components'] ?? null;
    }

    public function getCountryCode($lat, $lng)
    {
        $components = $this->reverseGeocode($lat, $lng);

        if (!$components) return null;

        foreach ($components as $component) {
            if (in_array('country', $component['types'])) {
                return $component['short_name'];
            }
        }

        return null;
    }

    public function getCity($lat, $lng)
    {
        $components = $this->reverseGeocode($lat, $lng);

        if (!$components) return null;

        foreach ($components as $component) {
            if (in_array('locality', $component['types'])) {
                return $component['long_name'];
            }
        }

        return null;
    }
}
