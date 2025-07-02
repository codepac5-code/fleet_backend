<?php
namespace App\Http\Services\Dashboard\BookingManagement\ChangeOrderStatus\Controller;

use App\Http\Services\Dashboard\BookingManagement\ChangeOrderStatus\Logic\ChangeOrderStatusInput;
use App\Http\Services\Dashboard\BookingManagement\ChangeOrderStatus\Logic\ChangeOrderStatusLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\BookingManagement\ChangeOrderStatus\Request\ChangeOrderStatusRequest;

class ChangeOrderStatusController extends Controller
{
    public function __invoke(ChangeOrderStatusRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new ChangeOrderStatusInput($request->validated());

        $service = new ChangeOrderStatusLogic($input); // call the service's logic

        // execute service and get result..
        return $service->execute(); // send response..
    }
}