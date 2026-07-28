<?php

namespace App\Http\Services\Panel\Home\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Home\Logic\DashboardData;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HomeStatsController extends Controller
{
    public function __invoke(Request $request, EntityScope $scope): JsonResponse
    {
        $from = $request->date('from');
        $to   = $request->date('to');

        if (! $from || ! $to) {
            return response()->json(['stats' => []], 422);
        }

        if ($from->greaterThan($to)) {
            [$from, $to] = [$to, $from];
        }

        $data  = new DashboardData($scope);
        $stats = array_map(fn ($m) => [
            'label' => $m['label'],
            'icon'  => $m['icon'],
            'value' => $m['money'] ? getPriceFormat($m['value']) : number_format((int) $m['value']),
        ], $data->rangeStats($from, $to));

        return response()->json([
            'stats'  => $stats,
            'status' => $data->statusBreakdown($from->copy()->startOfDay(), $to->copy()->endOfDay()),
        ]);
    }
}
