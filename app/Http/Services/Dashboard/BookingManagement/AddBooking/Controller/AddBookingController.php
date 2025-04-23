<?php
namespace App\Http\Services\Dashboard\BookingManagement\AddBooking\Controller;

use App\Http\Services\Dashboard\BookingManagement\AddBooking\Logic\AddBookingInput;
use App\Http\Services\Dashboard\BookingManagement\AddBooking\Logic\AddBookingLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\BookingManagement\AddBooking\Request\AddBookingRequest;

class AddBookingController extends Controller
{
    public function __invoke(AddBookingRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new AddBookingInput($request->validated());

        $service = new AddBookingLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}