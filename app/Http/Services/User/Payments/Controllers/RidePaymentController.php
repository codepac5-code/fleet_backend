<?php

namespace App\Http\Services\User\Payments\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Services\User\Payments\Logic\RideCardPaymentService;
use App\Http\Services\User\Support\Reply;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RidePaymentController extends Controller
{
    public function __construct(private RideCardPaymentService $rides)
    {
    }

    public function authorize(Request $request): JsonResponse
    {
        $data = $request->validate([
            'office_id' => ['required', 'integer', 'min:1'],
            'service' => ['nullable', 'string', 'max:40'],
            'service_class' => ['required', 'string', 'max:60'],
            'sub_service_id' => ['nullable', 'integer', 'min:1'],
            'pickup_lat' => ['required', 'numeric', 'between:-90,90'],
            'pickup_lng' => ['required', 'numeric', 'between:-180,180'],
            'dropoff_lat' => ['required', 'numeric', 'between:-90,90'],
            'dropoff_lng' => ['required', 'numeric', 'between:-180,180'],
            'distance_m' => ['nullable', 'integer', 'min:0'],
            'duration_s' => ['nullable', 'integer', 'min:0'],
        ]);

        $in = [
            'office_id' => (int) $data['office_id'],
            'service' => $data['service'] ?? 'ride',
            'service_class' => (string) $data['service_class'],
            'sub_service_id' => isset($data['sub_service_id']) ? (int) $data['sub_service_id'] : 0,
            'pickup' => ['lat' => (float) $data['pickup_lat'], 'lng' => (float) $data['pickup_lng']],
            'dropoff' => ['lat' => (float) $data['dropoff_lat'], 'lng' => (float) $data['dropoff_lng']],
            'distance_m' => (int) ($data['distance_m'] ?? 0),
            'duration_s' => (int) ($data['duration_s'] ?? 0),
        ];

        return Reply::ok($this->rides->authorize(
            (int) $request->user()->id,
            $in,
            (string) $request->header('Idempotency-Key', '')
        ));
    }

    public function intent(Request $request, int $id): JsonResponse
    {
        return Reply::ok($this->rides->intent((int) $request->user()->id, $id));
    }

    public function verify(Request $request, int $id): JsonResponse
    {
        $data = $request->validate(['paymentIntentId' => ['required', 'string']]);

        return Reply::ok($this->rides->verify((int) $request->user()->id, $id, $data['paymentIntentId']));
    }
}
