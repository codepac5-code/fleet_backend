<?php
namespace App\Http\Services\Dashboard\BookingManagement\DeleteBooking\Controller;

use App\Http\Services\Dashboard\BookingManagement\DeleteBooking\Logic\DeleteBookingInput;
use App\Http\Services\Dashboard\BookingManagement\DeleteBooking\Logic\DeleteBookingLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use Illuminate\Http\Request;

class DeleteBookingController extends Controller
{
    public function __invoke(Request $request)
    {
        // validate input data and pass it to the service..
        $input = new DeleteBookingInput($request->all());

        $service = new DeleteBookingLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}