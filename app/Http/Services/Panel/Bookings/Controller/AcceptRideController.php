<?php

namespace App\Http\Services\Panel\Bookings\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Ride\FixedTripService;
use App\Http\Core\Const\Ride\BookingStatus;
use App\Http\Services\Panel\Bookings\Logic\BookingRepository;
use App\Http\Services\Panel\Bookings\Logic\ScheduledTripPresenter;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\RideBooking;
use Illuminate\Http\JsonResponse;

class AcceptRideController extends Controller
{
    public function __invoke(
        int $ride,
        BookingRepository $bookings,
        EntityScope $scope,
        FixedTripService $fixed
    ): JsonResponse {
        $booking = RideBooking::on(TenantConnection::current())->findOrFail($ride);

        $officeId = $scope->officeId();
        if ($officeId !== null && (int) $booking->office_id !== $officeId) {
            abort(403);
        }

        if ((string) $booking->status !== BookingStatus::PENDING_ACCEPTANCE) {
            return response()->json([
                'ok'      => false,
                'message' => textByLanguage('لا يمكن قبول هذه الرحلة في حالتها الحالية', 'This trip cannot be accepted in its current state'),
            ], 422);
        }

        // Fixed corridor trips carry the office-acceptance step (PENDING → CONFIRMED)
        // and release/re-hold escrow through FixedTripService; reuse it so the panel
        // and the office app confirm a trip identically. Admin acts on the trip's
        // own office; an office may only accept its own.
        $fixed->accept($officeId ?? (int) $booking->office_id, (int) $booking->id);

        $row = $bookings->findScheduledRow($booking->id);

        return response()->json([
            'ok'      => true,
            'message' => textByLanguage('تم قبول الرحلة', 'Trip accepted'),
            'trip'    => $row ? ScheduledTripPresenter::toArray($row, $scope->guard()) : null,
        ]);
    }
}
