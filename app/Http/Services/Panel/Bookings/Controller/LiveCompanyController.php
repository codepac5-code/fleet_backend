<?php

namespace App\Http\Services\Panel\Bookings\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Bookings\Logic\LiveBoardRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LiveCompanyController extends Controller
{
    public function __invoke(Request $request, LiveBoardRepository $board): JsonResponse
    {
        $company = (string) $request->query('company', 'fleet');

        if ($company !== 'fleet' && ! ctype_digit($company)) {
            $company = 'fleet';
        }

        return response()->json($board->companyTrips($company));
    }
}
