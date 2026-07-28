<?php

namespace App\Http\Services\Driver\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Services\User\Support\Reply;
use App\Models\RideBooking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Nearby demand / queue hints for the cockpit map (`GET /driver/zones/demand`).
 * Derived from recent booking pickups grouped by area (last 90 min); a wait
 * level + surge hint is computed from the count.
 */
class DriverZoneController extends Controller
{
    public function demand(Request $request): JsonResponse
    {
        $rows = RideBooking::query()
            ->where('created_at', '>=', now()->subMinutes(90))
            ->whereNotNull('pickup_title')
            ->get(['pickup_title', 'pickup_lat', 'pickup_lng']);

        $groups = [];
        foreach ($rows as $r) {
            $key = (string) $r->pickup_title;
            if (! isset($groups[$key])) {
                $groups[$key] = ['zone' => $key, 'count' => 0, 'lat' => (float) $r->pickup_lat, 'lng' => (float) $r->pickup_lng];
            }
            $groups[$key]['count']++;
        }

        $groups = array_values($groups);
        usort($groups, fn ($a, $b) => $b['count'] <=> $a['count']);

        $items = array_map(fn (array $g) => [
            'zone' => $g['zone'],
            'wait' => $g['count'] >= 5 ? 'high' : ($g['count'] >= 2 ? 'medium' : 'low'),
            'surge' => $g['count'] >= 5 ? 1.5 : ($g['count'] >= 2 ? 1.2 : 1.0),
            'lat' => $g['lat'],
            'lng' => $g['lng'],
        ], array_slice($groups, 0, 5));

        return Reply::ok(['items' => $items]);
    }
}
