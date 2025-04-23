<?php
namespace App\Http\Services\Apis\SyriatelPaymentApi\Controller;

use App\Http\Services\Apis\SyriatelPaymentApi\Logic\SyriatelPaymentApiInput;
use App\Http\Services\Apis\SyriatelPaymentApi\Logic\SyriatelPaymentApiLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Apis\SyriatelPaymentApi\Request\SyriatelPaymentApiRequest;

class SyriatelPaymentApiController extends Controller
{
    public function __invoke(SyriatelPaymentApiRequest $request)
    {
        
        // validate input data and pass it to the service..
        $input = new SyriatelPaymentApiInput($request->validated());

        $service = new SyriatelPaymentApiLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}