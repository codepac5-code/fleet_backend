<?php
namespace App\Http\Services\Driver\ReceiveCash\Controller;

use App\Http\Services\Driver\ReceiveCash\Logic\ReceiveCashInput;
use App\Http\Services\Driver\ReceiveCash\Logic\ReceiveCashLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Driver\ReceiveCash\Request\ReceiveCashRequest;

class ReceiveCashController extends Controller
{
    public function __invoke(ReceiveCashRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new ReceiveCashInput($request->validated());

        $service = new ReceiveCashLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}