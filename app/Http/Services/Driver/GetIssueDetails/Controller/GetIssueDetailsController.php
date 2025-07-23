<?php
namespace App\Http\Services\Driver\GetIssueDetails\Controller;

use App\Http\Services\Driver\GetIssueDetails\Logic\GetIssueDetailsInput;
use App\Http\Services\Driver\GetIssueDetails\Logic\GetIssueDetailsLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Driver\GetIssueDetails\Request\GetIssueDetailsRequest;

class GetIssueDetailsController extends Controller
{
    public function __invoke(GetIssueDetailsRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new GetIssueDetailsInput($request->validated());

        $service = new GetIssueDetailsLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}