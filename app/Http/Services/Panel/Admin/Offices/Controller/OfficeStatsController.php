<?php

namespace App\Http\Services\Panel\Admin\Offices\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Admin\Offices\Logic\OfficeAnalytics;
use App\Http\Services\Panel\Admin\Offices\Logic\OfficeRepository;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OfficeStatsController extends Controller
{
    public function __invoke(Request $request, int $office, OfficeRepository $offices, OfficeAnalytics $analytics): JsonResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
        ]);

        $model = $offices->findOrFail($office);
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
