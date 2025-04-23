<?php
namespace App\Http\Services\User\Auth\UserSendOtpServiceService\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\User\Auth\UserSendOtpServiceService\Logic\UserSendOtpServiceServiceInput;
use App\Http\Services\User\Auth\UserSendOtpServiceService\Logic\UserSendOtpServiceServiceLogic;
use App\Http\Services\User\Auth\UserSendOtpServiceService\Request\UserSendOtpServiceServiceRequest;

class UserSendOtpServiceServiceController extends Controller
{
    public function __invoke(UserSendOtpServiceServiceRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new UserSendOtpServiceServiceInput($request->validated());

        $service = new UserSendOtpServiceServiceLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}
