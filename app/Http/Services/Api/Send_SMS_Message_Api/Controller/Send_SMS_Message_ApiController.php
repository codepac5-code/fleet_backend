<?php
namespace App\Http\Services\Api\Send_SMS_Message_Api\Controller;

use App\Http\Services\Api\Send_SMS_Message_Api\Logic\Send_SMS_Message_ApiInput;
use App\Http\Services\Api\Send_SMS_Message_Api\Logic\Send_SMS_Message_ApiLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Api\Send_SMS_Message_Api\Request\Send_SMS_Message_ApiRequest;

class Send_SMS_Message_ApiController extends Controller
{
    public function __invoke(Send_SMS_Message_ApiRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new Send_SMS_Message_ApiInput($request->validated());

        $service = new Send_SMS_Message_ApiLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}