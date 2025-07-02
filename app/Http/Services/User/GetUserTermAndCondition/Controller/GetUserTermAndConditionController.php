<?php
namespace App\Http\Services\User\GetUserTermAndCondition\Controller;

use App\Http\Services\User\GetUserTermAndCondition\Logic\GetUserTermAndConditionInput;
use App\Http\Services\User\GetUserTermAndCondition\Logic\GetUserTermAndConditionLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\User\GetUserTermAndCondition\Request\GetUserTermAndConditionRequest;

class GetUserTermAndConditionController extends Controller
{
    public function __invoke(GetUserTermAndConditionRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new GetUserTermAndConditionInput($request->validated());

        $service = new GetUserTermAndConditionLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}