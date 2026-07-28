<?php

namespace App\Http\Services\Panel\Bookings\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Ride\RideLifecycleService;
use App\Http\Core\Const\Dispatch\DispatchStatus;
use App\Http\Core\Const\Options\Guard;
use App\Http\Core\GeoServices\ShardManager;
use App\Models\DispatchJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class SettleBookingController extends Controller
{
    public function __invoke(Request $request, int $booking, RideLifecycleService $rides): JsonResponse
    {
        $officeId = (int) Auth::guard(Guard::$Office)->id();

        $job = DispatchJob::query()
            ->where('booking_id', $booking)
            ->where('office_id', $officeId)
            ->where('status', DispatchStatus::ASSIGNED)
            ->whereNotNull('assigned_driver_id')
            ->first();

        if ($job === null) {
            return response()->json(['error' => ['code' => 'ride_not_settleable', 'message' => 'No assigned ride for this booking.']], 422);
        }

        $totalMinor = (int) $request->input('total_minor');

        if ($totalMinor <= 0) {
            return response()->json(['error' => ['code' => 'validation_failed', 'message' => 'total_minor must be positive.']], 422);
        }

        $paymentMethod = (string) $request->input('payment_method', 'digital');
        $currency = strtoupper((string) ($request->input('currency_code') ?: ShardManager::currency()));

        $payload = [
            'booking_id' => $booking,
            'office_id' => $officeId,
            'driver_id' => (int) $job->assigned_driver_id,
            'currency_code' => $currency,
            'total_minor' => $totalMinor,
            'fare_minor' => (int) $request->input('fare_minor', $totalMinor),
            'discount_minor' => (int) $request->input('discount_minor', 0),
            'pricing_style' => (string) $request->input('pricing_style', 'meter'),
        ];

        try {
            $transaction = $rides->settle($payload, $paymentMethod);
        } catch (Throwable $e) {
            return response()->json(['error' => ['code' => 'settlement_error', 'message' => $e->getMessage()]], 422);
        }

        return response()->json(['data' => [
            'booking_id' => $booking,
            'driver_id' => (int) $job->assigned_driver_id,
            'payment_method' => strtolower($paymentMethod),
            'total_minor' => $totalMinor,
            'ledger_transaction_uuid' => $transaction->uuid,
        ]], 201);
    }
}
