<?php

namespace App\Http\Services\Driver\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Ride\DriverTripService;
use App\Http\Services\User\Support\Reply;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Driver ride lifecycle. Every action is a REST POST that mutates the booking
 * and emits `booking.status_changed` (relayed to the rider by the gateway).
 */
class DriverTripController extends Controller
{
    public function __construct(private DriverTripService $trips)
    {
    }

    private function driverId(Request $request): int
    {
        return (int) $request->user()->id;
    }

    public function navigatePickup(Request $request, int $id): JsonResponse
    {
        return Reply::ok($this->trips->navigateToPickup($this->driverId($request), $id));
    }

    public function arrived(Request $request, int $id): JsonResponse
    {
        return Reply::ok($this->trips->arrived($this->driverId($request), $id));
    }

    public function start(Request $request, int $id): JsonResponse
    {
        return Reply::ok($this->trips->startTrip($this->driverId($request), $id));
    }

    public function end(Request $request, int $id): JsonResponse
    {
        $meter = $request->validate([
            'distance_m' => ['nullable', 'integer', 'min:0'],
            'duration_s' => ['nullable', 'integer', 'min:0'],
            'waiting_s' => ['nullable', 'integer', 'min:0'],
        ]);

        return Reply::ok($this->trips->endTrip($this->driverId($request), $id, $meter));
    }

    public function confirmPayment(Request $request, int $id): JsonResponse
    {
        return Reply::ok($this->trips->confirmPayment($this->driverId($request), $id));
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        return Reply::ok($this->trips->cancel($this->driverId($request), $id, $request->input('reason')));
    }

    public function location(Request $request, int $id): Response
    {
        $data = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'heading' => ['nullable', 'numeric'],
            'eta_seconds' => ['nullable', 'integer', 'min:0'],
        ]);

        $this->trips->updateLocation(
            $this->driverId($request),
            $id,
            (float) $data['lat'],
            (float) $data['lng'],
            isset($data['heading']) ? (float) $data['heading'] : null,
            isset($data['eta_seconds']) ? (int) $data['eta_seconds'] : null,
        );

        return response()->noContent();
    }
}
