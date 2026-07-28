<?php

namespace App\Http\Services\Panel\Home\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Home\Logic\DashboardData;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Http\JsonResponse;

class HomeLiveController extends Controller
{
    public function __invoke(EntityScope $scope): JsonResponse
    {
        $data = new DashboardData($scope);

        return response()->json([
            'values' => $data->liveValues(),
            'time'   => now()->format('H:i:s'),
        ]);
    }
}
