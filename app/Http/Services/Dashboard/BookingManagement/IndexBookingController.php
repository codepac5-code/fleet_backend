<?php
namespace App\Http\Services\Dashboard\BookingManagement;

use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Response\SendResponse;
use App\Http\Controllers\Controller;
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
        return view('booking.view', compact('pageTitle','auth_user','assets','filter'));
    }
}