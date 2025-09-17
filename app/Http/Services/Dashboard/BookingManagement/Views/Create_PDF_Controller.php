<?php
namespace App\Http\Services\Dashboard\BookingManagement\Views;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\Booking;
use Barryvdh\DomPDF\Facade\Pdf;

class Create_PDF_Controller extends Controller
{
    public function __invoke( Request $request , $id )
    {

        $booking = Booking::with(['driver.office', 'office', 'user', 'subService.service', 'coupon'])->findOrFail($id);
        $pdf = Pdf::loadView('booking.invoice', compact('booking'));
        return $pdf->download('invoice.pdf');

        // $data =AppSetting::take(1)->first();
        // // $bookingdata = Booking::with('handymanAdded', 'payment', 'bookingExtraCharge')->myBooking()->find($id);
        // $bookingdata = Booking::with('driver', 'payment', 'service')->find($id);
        // $pdf = Pdf::loadView('booking.invoice',['bookingdata'=>$bookingdata ,'data'=> $data]);
        // return $pdf->download('invoice.pdf');
        
    }
}
