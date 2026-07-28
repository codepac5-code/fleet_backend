<?php

namespace App\Http\Services\Panel\Bookings\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Bookings\Logic\LiveBoardRepository;
use Illuminate\Http\JsonResponse;

class LiveSummaryController extends Controller
{
    public function __invoke(LiveBoardRepository $board): JsonResponse
    {
        return response()->json($board->summary());
    }
}
