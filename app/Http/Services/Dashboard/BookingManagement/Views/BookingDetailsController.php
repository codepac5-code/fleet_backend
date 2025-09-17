<?php
namespace App\Http\Services\Dashboard\BookingManagement\Views;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Booking;

class BookingDetailsController extends Controller
{
    public function __invoke( Request $request  )
    {
        $auth_user = authSession();
        $user = auth()->user();
        // $user->last_notification_seen = now();
        $user->save();
        $id= $request->input('id');

        $bookingdata = Booking::find($id);
        $tabpage = 'info';
       if ($bookingdata == null) {
           $msg = __('messages.not_found_entry', ['name' => __('messages.booking')]);
           return redirect()->back()->withError($msg);
       }

       $subservice = $bookingdata->subservice;
       $total_price = ($subservice->openPrice+ $subservice->kmPrice * $bookingdata->distance + $subservice->minutePrice *  (int)$bookingdata->time );
       $driver = $bookingdata->driver;
       $car = $driver->vehicle ?? 'unknow';
       $office = $bookingdata->office;
       $user = $bookingdata->user;

       $withOffice = false;
       if($office != null){
        $withOffice = true;

       }
       $pageTitle = __('messages.view_form_title', ['form' => __('messages.booking')]);
       return view('booking.info', compact('pageTitle','user','office','withOffice',
        'driver', 'bookingdata', 'auth_user', 'tabpage','subservice' ,'total_price','car'));
    }
}
