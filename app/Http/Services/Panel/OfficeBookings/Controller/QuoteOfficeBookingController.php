<?php

namespace App\Http\Services\Panel\OfficeBookings\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Ride\OfficeBookingService;
use App\Http\Core\Exceptions\DomainException;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuoteOfficeBookingController extends Controller
{
    public function __invoke(Request $request, EntityScope $scope, OfficeBookingService $bookings): JsonResponse
    {
        $data = $request->validate([
            'office_id' => ['nullable', 'integer'],
            'service' => ['required', 'string', 'max:16'],
            'service_class' => ['required', 'string', 'max:32'],
            'pickup_lat' => ['required', 'numeric', 'between:-90,90'],
            'pickup_lng' => ['required', 'numeric', 'between:-180,180'],
            'dropoff_lat' => ['required', 'numeric', 'between:-90,90'],
            'dropoff_lng' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $officeId = $scope->isAdmin() ? (int) ($data['office_id'] ?? 0) : (int) $scope->officeId();

        if ($officeId <= 0) {
            return response()->json(['error' => 'office_required'], 422);
        }

        try {
            $quote = $bookings->quote(
                $officeId,
                $data['service'],
                $data['service_class'],
                (float) $data['pickup_lat'],
                (float) $data['pickup_lng'],
                (float) $data['dropoff_lat'],
                (float) $data['dropoff_lng']
            );
        } catch (DomainException $e) {
            return response()->json(['error' => $e->getMessage()], $e->status ?? 422);
        }

        return response()->json(['data' => $quote]);
    }
}
