<?php

namespace App\Http\Services\Panel\Subscriptions\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Subscription\OfficeSubscriptionService;
use Illuminate\Http\JsonResponse;

class ShowOfficeSubscriptionController extends Controller
{
    public function __invoke(int $office, OfficeSubscriptionService $subscriptions): JsonResponse
    {
        $subscription = $subscriptions->activeFor($office);

        return response()->json(['data' => $subscription === null ? null : [
            'office_id' => $office,
            'plan_key' => $subscription->plan_key,
            'fleet_commission_rate' => (float) $subscription->fleet_commission_rate,
            'office_commission_rate' => (float) $subscription->office_commission_rate,
            'price_minor' => (int) $subscription->price_minor,
            'currency_code' => $subscription->currency_code,
            'status' => $subscription->status,
            'started_at' => $subscription->started_at,
        ]]);
    }
}
