<?php
namespace App\Http\Services\Dashboard\BookingManagement\ShowBooking\Controller;

use App\Http\Services\Dashboard\BookingManagement\ShowBooking\Logic\ShowBookingInput;
use App\Http\Services\Dashboard\BookingManagement\ShowBooking\Logic\ShowBookingLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use Illuminate\Http\Request;

class ShowBookingController extends Controller
{
    public function __invoke(Request $request)
    {
        // validate input data and pass it to the service..
        $input = new ShowBookingInput($request->all());

        $service = new ShowBookingLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}