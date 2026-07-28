<?php

namespace App\Http\Services\Panel\Dispatch\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Dispatch\DispatchService;
use App\Http\Core\Const\Options\Guard;
use App\Models\DispatchJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class OfferWaveController extends Controller
{
    public function __invoke(Request $request, int $booking, DispatchService $dispatch): JsonResponse
    {
        $officeId = (int) Auth::guard(Guard::$Office)->id();

        $owns = DispatchJob::query()
            ->where('booking_id', $booking)
            ->where('office_id', $officeId)
            ->exists();

        if (!$owns) {
            return response()->json(['error' => ['code' => 'not_found', 'message' => 'No dispatch job for this booking.']], 404);
        }

        $ttl = (int) $request->input('ttl_seconds', 20);
        $radius = (float) $request->input('radius_meters', 5000);
        $limit = min(20, max(1, (int) $request->input('limit', 5)));

        try {
            $created = $dispatch->offerWave($booking, $ttl, $radius, $limit);
        } catch (Throwable $e) {
            return response()->json(['error' => ['code' => 'offer_failed', 'message' => $e->getMessage()]], 422);
        }

        $offers = array_map(fn ($offer) => [
            'driver_id' => (int) $offer->driver_id,
            'distance_m' => (float) $offer->distance_m,
            'expires_at' => $offer->expires_at,
        ], $created);

        return response()->json(['data' => ['booking_id' => $booking, 'offers' => $offers]]);
    }
}
