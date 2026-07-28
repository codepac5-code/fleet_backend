<?php

namespace App\Http\Controllers;

use App\Http\Core\Classes\Ride\RideBookingService;
use Illuminate\Contracts\View\View;

class SharedTripController extends Controller
{
    public function __invoke(string $slug, RideBookingService $bookings): View
    {
        $dash = strpos($slug, '-');

        if ($dash === false) {
            abort(404);
        }

        $id = (int) substr($slug, 0, $dash);
        $token = substr($slug, $dash + 1);

        $trip = $id > 0 ? $bookings->sharedView($id, $token) : null;

        if ($trip === null) {
            abort(404);
        }

        return view('public.shared-trip', ['trip' => $trip]);
    }
}
