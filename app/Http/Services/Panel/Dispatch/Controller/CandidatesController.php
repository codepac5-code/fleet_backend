<?php

namespace App\Http\Services\Panel\Dispatch\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Dispatch\DispatchService;
use App\Http\Core\Const\Options\Guard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CandidatesController extends Controller
{
    public function __invoke(Request $request, DispatchService $dispatch): JsonResponse
    {
        $officeId = (int) Auth::guard(Guard::$Office)->id();
        $lat = (float) $request->query('lat');
        $lng = (float) $request->query('lng');
        $radius = (float) $request->query('radius_meters', 5000);
        $limit = min(50, max(1, (int) $request->query('limit', 10)));

        $candidates = $dispatch->findCandidates($officeId, $lat, $lng, $radius, $limit);

        return response()->json(['data' => $candidates]);
    }
}
