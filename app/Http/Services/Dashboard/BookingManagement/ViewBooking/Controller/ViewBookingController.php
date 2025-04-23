<?php
namespace App\Http\Services\Dashboard\BookingManagement\ViewBooking\Controller;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\BookingManagement\ViewBooking\Logic\ViewBookingInput;
use App\Http\Services\Dashboard\BookingManagement\ViewBooking\Logic\ViewBookingLogic;

class ViewBookingController extends Controller
{
    public function __invoke(Request $request)
    {
        // validate input data and pass it to the service..
        $input = new ViewBookingInput($request->all());

        $service = new ViewBookingLogic($input); // call the service's logic

        // execute service and get result..
        return $service->execute();  // send response..
    }
}