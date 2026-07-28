<?php

namespace App\Http\Services\User\Booking\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Services\User\Support\Presenters\CancellationReasonPresenter;
use App\Http\Services\User\Support\Reply;
use App\Models\CancellationReason;
use Illuminate\Http\JsonResponse;

class CancellationReasonsController extends Controller
{
    public function index(): JsonResponse
    {
        return Reply::ok([
            'reasons' => CancellationReasonPresenter::forAudience(CancellationReason::AUDIENCE_RIDER),
        ]);
    }
}
