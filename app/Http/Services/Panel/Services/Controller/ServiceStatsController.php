<?php

namespace App\Http\Services\Panel\Services\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Services\Logic\ServiceAnalytics;
use App\Http\Services\Panel\Services\Logic\ServiceRepository;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceStatsController extends Controller
{
    public function __invoke(Request $request, int $service, ServiceRepository $services, ServiceAnalytics $analytics): JsonResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
        ]);

        $model = $services->findOrFail($service);
        $date = Carbon::createFromFormat('Y-m-d', $validated['date']);

        $stats = $analytics->statsForDate($model, $date);

        return response()->json([
            'date'    => $date->format('Y-m-d'),
            'label'   => $date->translatedFormat('l، j F Y'),
            'trips'   => $stats['trips'],
            'revenue' => getPriceFormat($stats['revenue']),
        ]);
    }
}
