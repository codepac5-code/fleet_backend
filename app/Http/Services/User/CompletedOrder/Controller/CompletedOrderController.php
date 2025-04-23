<?php
namespace App\Http\Services\User\CompletedOrder\Controller;

use App\Http\Services\User\CompletedOrder\Logic\CompletedOrderInput;
use App\Http\Services\User\CompletedOrder\Logic\CompletedOrderLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\User\CompletedOrder\Request\CompletedOrderRequest;

class CompletedOrderController extends Controller
{
    public function __invoke(CompletedOrderRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new CompletedOrderInput($request->validated());

        $service = new CompletedOrderLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}