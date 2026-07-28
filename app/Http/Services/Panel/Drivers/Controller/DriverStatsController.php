<?php

namespace App\Http\Services\Panel\Drivers\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Drivers\Logic\DriverProfile;
use App\Http\Services\Panel\Drivers\Logic\DriverRepository;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DriverStatsController extends Controller
{
    public function __invoke(Request $request, int $driver, DriverRepository $drivers, DriverProfile $profile): JsonResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
        ]);

        $model = $drivers->findOrFail($driver);
        $date = Carbon::createFromFormat('Y-m-d', $validated['date']);

        $stats = $profile->statsForDate($model, $date);

        return response()->json([
            'date'    => $date->format('Y-m-d'),
            'label'   => $date->translatedFormat('l، j F Y'),
            'trips'   => $stats['trips'],
            'revenue' => getPriceFormat($stats['revenue']),
        ]);
    }
}
