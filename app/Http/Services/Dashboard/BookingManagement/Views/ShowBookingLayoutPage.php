<?php
namespace App\Http\Services\Dashboard\BookingManagement\Views;

use App\Models\User;
use App\Models\Driver;
use App\Models\Office;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Http\Services\Dashboard\ServiceManagement\AddService\Request\AddServiceRequest;
use App\Models\Booking;
use App\Models\City;
use App\Models\Country;

class ShowBookingLayoutPage extends Controller
{
    public function __invoke (Request $request , $id)
    {

        $tabpage = $request->tabpage;
        $auth_user = authSession();
        $user_id = $auth_user->id;
        $user_data = User::find($user_id);
        // $bookingdata = Booking::with('driver', 'payment', 'bookingExtraCharge', 'bookingAddonService')->myBooking()->find($id);

        $bookingdata = Booking::with('driver', 'payment', 'service','user')->find($id);
        switch ($tabpage) {
            case 'info':
                $data  = view('booking.' . $tabpage, compact('user_data', 'tabpage', 'auth_user', 'bookingdata'))->render();
                break;
            case 'status':
                $data  = view('booking.' . $tabpage, compact('user_data', 'tabpage', 'auth_user', 'bookingdata'))->render();
                break;
            default:
                $data  = view('booking.' . $tabpage, compact('tabpage', 'auth_user', 'bookingdata'))->render();
                break;
        }
        return response()->json($data);
    }
}
