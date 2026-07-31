<?php

namespace App\Http\Services\Panel\Bookings\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Dispatch\DispatchService;
use App\Http\Core\Classes\Event\DomainEvent;
use App\Http\Core\Classes\Event\EventBus;
use App\Http\Core\Classes\Ledger\FleetWalletService;
use App\Http\Core\Const\Event\Channel;
use App\Http\Core\Const\Event\EventType;
use App\Http\Core\Const\Ride\BookingSource;
use App\Http\Core\Const\Ride\BookingStatus;
use App\Http\Services\Panel\Bookings\Logic\BookingRepository;
use App\Http\Services\Panel\Bookings\Logic\ScheduledTripPresenter;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\RideBooking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CancelRideController extends Controller
{
    public function __invoke(
        Request $request,
        int $ride,
        BookingRepository $bookings,
        EntityScope $scope,
        FleetWalletService $wallet,
        DispatchService $dispatch,
        EventBus $events
    ): JsonResponse {
        $conn    = TenantConnection::current();
        $booking = RideBooking::on($conn)->findOrFail($ride);

        $officeId = $scope->officeId();
        if ($officeId !== null && (int) $booking->office_id !== $officeId) {
            abort(403);
        }

        if (in_array((string) $booking->status, BookingStatus::TERMINAL, true)) {
            return response()->json([
                'ok'      => false,
                'message' => textByLanguage('هذه الرحلة منتهية ولا يمكن إلغاؤها', 'This trip is already closed'),
            ], 422);
        }

        if (in_array((string) $booking->status, BookingStatus::LIVE_SUB, true)) {
            return response()->json([
                'ok'      => false,
                'message' => textByLanguage('السائق في الطريق — ألغِ الرحلة من لوح الرحلات الحيّة', 'The driver is already en route — cancel from the live board'),
            ], 422);
        }

        $reason   = trim((string) $request->input('reason')) ?: 'cancelled_by_panel';
        $driverId = $booking->driver_id ? (int) $booking->driver_id : null;

        DB::transaction(function () use ($booking, $wallet, $dispatch, $reason, $driverId) {
            // Give the rider their money back: release any escrow still held for
            // this booking (a scheduled ride's fare is held at activation).
            $held = $wallet->escrowBalanceMinor((int) $booking->id, (string) $booking->currency_code);
            if ($held > 0) {
                $wallet->refundFromEscrow(
                    (int) $booking->id,
                    (int) $booking->user_id,
                    $held,
                    (string) $booking->currency_code,
                    'cancel-void:' . $booking->id . ':' . $booking->office_id
                );
                $booking->held_minor = 0;
            }

            // Cancel the dispatch job in any active state and free the pre-assigned
            // driver so they can take new work.
            $dispatch->cancelForBooking((int) $booking->id, $driverId, $reason, 'panel');

            $booking->status       = BookingStatus::CANCELLED;
            $booking->cancelled_at = now();
            $booking->cancel_reason = $reason;
            $booking->save();
        });

        $channels = [
            Channel::office((int) $booking->office_id),
            Channel::user((int) $booking->user_id),
            Channel::booking((int) $booking->id),
        ];
        if ($driverId !== null) {
            $channels[] = Channel::driver($driverId);
        }

        $events->emit(new DomainEvent(
            EventType::BOOKING_STATUS_CHANGED,
            $channels,
            [
                'booking_id' => (int) $booking->id,
                'status'     => BookingStatus::CANCELLED,
                'source'     => BookingSource::OFFICE,
                'office_id'  => (int) $booking->office_id,
                'reason'     => $reason,
            ]
        ));

        $row = $bookings->findScheduledRow($booking->id);

        return response()->json([
            'ok'      => true,
            'message' => textByLanguage('تم إلغاء الرحلة', 'Trip cancelled'),
            'trip'    => $row ? ScheduledTripPresenter::toArray($row, $scope->guard()) : null,
        ]);
    }
}
