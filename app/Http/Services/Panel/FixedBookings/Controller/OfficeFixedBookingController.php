<?php

namespace App\Http\Services\Panel\FixedBookings\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Ride\FixedTripService;
use App\Http\Core\Const\Options\Guard;
use App\Http\Services\User\Support\Reply;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Office side of the fixed-trip flow: accept / decline a rider's offer, then
 * assign a driver. Scoped to the signed-in office (the service re-checks
 * ownership). Thin wrappers over FixedTripService.
 */
class OfficeFixedBookingController extends Controller
{
    public function __construct(private FixedTripService $fixed)
    {
    }

    public function accept(Request $request, int $booking): JsonResponse
    {
        return Reply::ok($this->fixed->accept($this->officeId(), $booking));
    }

    public function decline(Request $request, int $booking): JsonResponse
    {
        $reason = $request->input('reason');

        return Reply::ok($this->fixed->decline($this->officeId(), $booking, $reason !== null ? (string) $reason : null));
    }

    public function assignDriver(Request $request, int $booking): JsonResponse
    {
        $v = $request->validate(['driver_id' => ['required', 'integer']]);

        return Reply::ok($this->fixed->assignDriver($this->officeId(), $booking, (int) $v['driver_id']));
    }

    private function officeId(): int
    {
        return (int) Auth::guard(Guard::$Office)->id();
    }
}
