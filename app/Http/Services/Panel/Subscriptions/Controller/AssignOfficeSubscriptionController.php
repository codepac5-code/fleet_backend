<?php

namespace App\Http\Services\Panel\Subscriptions\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Subscription\OfficeSubscriptionService;
use App\Http\Core\GeoServices\ShardManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class AssignOfficeSubscriptionController extends Controller
{
    public function __invoke(Request $request, int $office, OfficeSubscriptionService $subscriptions): JsonResponse
    {
        $planKey = (string) $request->input('plan_key', '');
        $officeRate = (float) $request->input('office_rate', 0);
        $currency = $request->input('currency_code') ?: ShardManager::currency();
        $fleetOverride = $request->input('fleet_rate') !== null ? (float) $request->input('fleet_rate') : null;

        if ($planKey === '') {
            return response()->json(['error' => ['code' => 'validation_failed', 'message' => 'plan_key is required.']], 422);
        }

        try {
            $subscription = $subscriptions->subscribe($office, $planKey, $officeRate, $currency, $fleetOverride);
        } catch (Throwable $e) {
            return response()->json(['error' => ['code' => 'subscription_error', 'message' => $e->getMessage()]], 422);
        }

        return response()->json(['data' => [
            'office_id' => $office,
            'plan_key' => $subscription->plan_key,
            'fleet_commission_rate' => (float) $subscription->fleet_commission_rate,
            'office_commission_rate' => (float) $subscription->office_commission_rate,
            'price_minor' => (int) $subscription->price_minor,
            'currency_code' => $subscription->currency_code,
            'status' => $subscription->status,
        ]], 201);
    }
}
