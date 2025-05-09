<?php
namespace App\Http\Services\User\InquireUserOrderStatus\Controller;

use App\Http\Services\User\InquireUserOrderStatus\Logic\InquireUserOrderStatusInput;
use App\Http\Services\User\InquireUserOrderStatus\Logic\InquireUserOrderStatusLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\User\InquireUserOrderStatus\Request\InquireUserOrderStatusRequest;

class InquireUserOrderStatusController extends Controller
{
    public function __invoke(InquireUserOrderStatusRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new InquireUserOrderStatusInput($request->validated());

        $service = new InquireUserOrderStatusLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}