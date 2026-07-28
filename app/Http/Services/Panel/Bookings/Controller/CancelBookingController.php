<?php

namespace App\Http\Services\Panel\Bookings\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Const\Options\OrderStatus;
use App\Http\Services\Panel\Bookings\Logic\BookingRepository;
use App\Http\Services\Panel\Bookings\Logic\ScheduledTripPresenter;
use App\Http\Services\Panel\Bookings\Request\CancelBookingRequest;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class CancelBookingController extends Controller
{
    public function __invoke(
        CancelBookingRequest $request,
        int $booking,
        BookingRepository $bookings,
        EntityScope $scope
    ): RedirectResponse|JsonResponse {
        $order = $bookings->findOrFail($booking);

        $order->status      = OrderStatus::$Cancelled;
        $order->reason      = $request->validated('reason');
        $order->cancelledAt = now();
        $order->save();

        $message = textByLanguage('تم إلغاء الرحلة', 'Trip cancelled');

        if ($request->wantsJson()) {
            $row = $bookings->findScheduledRow($order->id);

            return response()->json([
                'ok'      => true,
                'message' => $message,
                'trip'    => $row ? ScheduledTripPresenter::toArray($row, $scope->guard()) : null,
            ]);
        }

        return back()->with('status', $message);
    }
}
