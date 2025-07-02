<?php
namespace App\Http\Services\Dashboard\BookingManagement;

use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Response\SendResponse;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Support\Facades\Auth;

class IndexBookingController extends Controller
{
    public function __invoke(Request $request)
    {
        $filter = [
            'status' => $request->status,
        ];
        $pageTitle = __('messages.bookings');
        $auth_user = 55; //authSession();
        $assets = ['datatable'];
        $completedCount = Booking::where('status', 'Completed')->count();

        return view('booking.follow.view', compact('pageTitle','auth_user','assets','filter','completedCount'));
    }
}