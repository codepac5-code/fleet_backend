<?php

namespace App\Http\Services\Panel\Bookings\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Bookings\Logic\BookingRepository;
use App\Http\Services\Panel\Bookings\Logic\BookingStatus;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Contracts\View\View;

class ShowBookingController extends Controller
{
    public function __invoke(int $booking, EntityScope $scope, BookingRepository $bookings): View
    {
        $model = $bookings->findOrFail($booking);

        return view('panel.bookings.show', [
            'entity'        => $scope->guard(),
            'user'          => $scope->user(),
            'isAdmin'       => $scope->isAdmin(),
            'booking'       => $model,
            'related'       => $bookings->details($model),
            'statusOptions' => BookingStatus::settable(),
        ]);
    }
}
