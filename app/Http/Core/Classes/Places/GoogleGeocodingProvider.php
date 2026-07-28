<?php

namespace App\Http\Core\Classes\Places;

use Illuminate\Support\Facades\Http;
use Throwable;

class GoogleGeocodingProvider implements GeocodingProvider
{
    private string $key;

    public function __construct(?string $key = null)
    {
        $this->key = (string) ($key ?? config('services.google_maps.key'));
    }

    public function autocomplete(string $query, ?float $lat, ?float $lng, ?string $session, ?string $country = null): array
    {
        try {
            $params = ['input' => $query, 'key' => $this->key];

            if ($lat !== null && $lng !== null) {
                $params['location'] = $lat . ',' . $lng;
                $params['radius'] = 50000;
            }

            // Restrict predictions to the user's country (ISO-2), so a rider in
            // Qatar only sees Qatari places for an ambiguous name.
            if ($country !== null && $country !== '') {
                $params['components'] = 'country:' . strtolower($country);
            }

            if ($session !== null && $session !== '') {
                $params['sessiontoken'] = $session;
            }

            $response = Http::get('https://maps.googleapis.com/maps/api/place/autocomplete/json', $params);
            $predictions = (array) $response->json('predictions', []);

            return array_map(fn ($p) => [
                'place_id' => (string) ($p['place_id'] ?? ''),
                'primary' => (string) ($p['structured_formatting']['main_text'] ?? ($p['description'] ?? '')),
                'secondary' => (string) ($p['structured_formatting']['secondary_text'] ?? ''),
                'kind' => $this->kind((array) ($p['types'] ?? [])),
            ], $predictions);
        } catch (Throwable $e) {
            return [];
        }
    }

    public function details(string $placeId): ?array
    {
        try {
            $response = Http::get('https://maps.googleapis.com/maps/api/place/details/json', [
                'place_id' => $placeId,
                'fields' => 'geometry,name,formatted_address,type',
                'key' => $this->key,
            ]);

            $result = (array) $response->json('result', []);

            if ($result === []) {
                return null;
            }

            return [
                'place_id' => $placeId,
                'title' => (string) ($result['name'] ?? ($result['formatted_address'] ?? '')),
                'lat' => (float) ($result['geometry']['location']['lat'] ?? 0),
                'lng' => (float) ($result['geometry']['location']['lng'] ?? 0),
                'kind' => $this->kind((array) ($result['types'] ?? [])),
            ];
        } catch (Throwable $e) {
            return null;
        }
    }

    public function reverse(float $lat, float $lng): ?array
    {
        try {
            $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
                'latlng' => $lat . ',' . $lng,
                'key' => $this->key,
            ]);

            $result = (array) ($response->json('results', [])[0] ?? []);

            if ($result === []) {
                return null;
            }

            return [
                'title' => (string) ($result['formatted_address'] ?? ''),
                'lat' => $lat,
                'lng' => $lng,
            ];
        } catch (Throwable $e) {
            return null;
        }
    }

    private function kind(array $types): ?string
    {
        return in_array('airport', $types, true) ? 'airport' : null;
    }
}
