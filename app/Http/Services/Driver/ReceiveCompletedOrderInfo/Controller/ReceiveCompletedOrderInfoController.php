<?php
namespace App\Http\Services\Driver\ReceiveCompletedOrderInfo\Controller;

use App\Http\Services\Driver\ReceiveCompletedOrderInfo\Logic\ReceiveCompletedOrderInfoInput;
use App\Http\Services\Driver\ReceiveCompletedOrderInfo\Logic\ReceiveCompletedOrderInfoLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Driver\ReceiveCompletedOrderInfo\Request\ReceiveCompletedOrderInfoRequest;

class ReceiveCompletedOrderInfoController extends Controller
{
    public function __invoke(ReceiveCompletedOrderInfoRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new ReceiveCompletedOrderInfoInput($request->validated());

        $service = new ReceiveCompletedOrderInfoLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}