<?php
namespace App\Http\Services\SharedServices\issues\SendIusseReply\Controller;

use App\Http\Services\SharedServices\issues\SendIusseReply\Logic\SendIusseReplyInput;
use App\Http\Services\SharedServices\issues\SendIusseReply\Logic\SendIusseReplyLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\SharedServices\issues\SendIusseReply\Request\SendIusseReplyRequest;

class SendIusseReplyController extends Controller
{
    public function __invoke(SendIusseReplyRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new SendIusseReplyInput($request->validated());

        $service = new SendIusseReplyLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}