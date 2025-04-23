<?php
namespace App\Http\Services\Dashboard\BookingManagement\EditBooking\Controller;

use App\Http\Services\Dashboard\BookingManagement\EditBooking\Logic\EditBookingInput;
use App\Http\Services\Dashboard\BookingManagement\EditBooking\Logic\EditBookingLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use Illuminate\Http\Request;

class EditBookingController extends Controller
{
    public function __invoke(Request $request)
    {
        // validate input data and pass it to the service..
        $input = new EditBookingInput($request->all());

        $service = new EditBookingLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}