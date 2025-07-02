<?php
namespace App\Http\Services\Dashboard\DriverManagement\GetOrderHistory\Controller;

use App\Http\Services\Dashboard\DriverManagement\GetOrderHistory\Logic\GetOrderHistoryInput;
use App\Http\Services\Dashboard\DriverManagement\GetOrderHistory\Logic\GetOrderHistoryLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\DriverManagement\GetOrderHistory\Request\GetOrderHistoryRequest;

class GetOrderHistoryController extends Controller
{
    public function __invoke(GetOrderHistoryRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new GetOrderHistoryInput($request->validated());

        $service = new GetOrderHistoryLogic($input); // call the service's logic

        // execute service and get result..
        return $service->execute();

        // return SendResponse::sendSuccessResponse($result); // send response..
    }
}