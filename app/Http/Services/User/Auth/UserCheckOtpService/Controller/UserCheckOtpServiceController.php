<?php
namespace App\Http\Services\User\Auth\UserCheckOtpService\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\User\Auth\UserCheckOtpService\Logic\UserCheckOtpServiceInput;
use App\Http\Services\User\Auth\UserCheckOtpService\Logic\UserCheckOtpServiceLogic;
use App\Http\Services\User\Auth\UserCheckOtpService\Request\UserCheckOtpServiceRequest;

class UserCheckOtpServiceController extends Controller
{
    public function __invoke(UserCheckOtpServiceRequest $request)
    {
        // validate input data and pass it to the service..

        $input = new UserCheckOtpServiceInput($request->validated());

        $service = new UserCheckOtpServiceLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}
