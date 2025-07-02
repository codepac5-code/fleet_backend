<?php
namespace App\Http\Services\User\GetReferralMessage\Controller;

use App\Http\Services\User\GetReferralMessage\Logic\GetReferralMessageInput;
use App\Http\Services\User\GetReferralMessage\Logic\GetReferralMessageLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\User\GetReferralMessage\Request\GetReferralMessageRequest;

class GetReferralMessageController extends Controller
{
    public function __invoke(GetReferralMessageRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new GetReferralMessageInput($request->validated());

        $service = new GetReferralMessageLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}