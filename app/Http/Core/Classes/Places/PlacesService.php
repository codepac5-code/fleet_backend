<?php

namespace App\Http\Core\Classes\Places;

use App\Http\Core\Repositories\Ride\RideBookingRepository;

class PlacesService
{
    public function __construct(
        private GeocodingProvider $geocoder,
        private RideBookingRepository $bookings
    ) {
    }

    public function autocomplete(string $query, ?float $lat, ?float $lng, ?string $session, ?string $country = null): array
    {
        $query = trim($query);

        if ($query === '') {
            return [];
        }

        return array_values(array_filter(
            $this->geocoder->autocomplete($query, $lat, $lng, $session, $country),
            fn ($row) => ($row['place_id'] ?? '') !== ''
        ));
    }

    public function details(string $placeId): ?array
    {
        return $this->geocoder->details($placeId);
    }

    public function reverse(float $lat, float $lng): ?array
    {
        return $this->geocoder->reverse($lat, $lng);
    }

    public function recent(int $userId, int $limit = 10): array
    {
        $rows = $this->bookings->recentDropoffsForUser($userId, 60);

        $seen = [];
        $recent = [];

        foreach ($rows as $row) {
            $key = round((float) $row->dropoff_lat, 4) . ',' . round((float) $row->dropoff_lng, 4);

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $recent[] = [
                'title' => $row->dropoff_title,
                'lat' => (float) $row->dropoff_lat,
                'lng' => (float) $row->dropoff_lng,
                'at' => optional($row->created_at)->toIso8601String(),
            ];

            if (count($recent) >= $limit) {
                break;
            }
        }

        return $recent;
    }
}
