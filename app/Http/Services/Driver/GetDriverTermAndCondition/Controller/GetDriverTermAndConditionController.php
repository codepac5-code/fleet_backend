<?php
namespace App\Http\Services\Driver\GetDriverTermAndCondition\Controller;

use App\Http\Services\Driver\GetDriverTermAndCondition\Logic\GetDriverTermAndConditionInput;
use App\Http\Services\Driver\GetDriverTermAndCondition\Logic\GetDriverTermAndConditionLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Driver\GetDriverTermAndCondition\Request\GetDriverTermAndConditionRequest;

class GetDriverTermAndConditionController extends Controller
{
    public function __invoke(GetDriverTermAndConditionRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new GetDriverTermAndConditionInput($request->validated());

        $service = new GetDriverTermAndConditionLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}