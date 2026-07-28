<?php

namespace App\Http\Services\Panel\Bookings\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Bookings\Logic\BookingRepository;
use App\Http\Services\Panel\Bookings\Request\UpdateBookingStatusRequest;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Http\RedirectResponse;

class UpdateBookingStatusController extends Controller
{
    public function __invoke(UpdateBookingStatusRequest $request, int $booking, EntityScope $scope, BookingRepository $bookings): RedirectResponse
    {
        $model = $bookings->findOrFail($booking);

        $bookings->updateStatus($model, $request->validated()['status']);

        return redirect()
            ->route("panel.{$scope->guard()}.booking.show", $model->id)
            ->with('status', textByLanguage('تم تحديث حالة الطلب', 'Order status updated'));
    }
}
