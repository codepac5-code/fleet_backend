<?php
namespace App\Http\Services\User\CancelOrder\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\User\CancelOrder\Logic\CancelOrderInput;
use App\Http\Services\User\CancelOrder\Logic\CancelOrderLogic;
use App\Http\Services\User\CancelOrder\Request\CancelOrderRequest;

class CancelOrderController extends Controller
{
    public function __invoke(CancelOrderRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new CancelOrderInput($request->all());

        $service = new CancelOrderLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}