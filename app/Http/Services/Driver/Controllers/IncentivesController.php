<?php

namespace App\Http\Services\Driver\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Incentive\IncentiveService;
use App\Http\Services\User\Support\Reply;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IncentivesController extends Controller
{
    public function __construct(private IncentiveService $incentives)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return Reply::ok([
            'incentives' => $this->incentives->progressFor((int) $request->user()->id),
        ]);
    }
}
