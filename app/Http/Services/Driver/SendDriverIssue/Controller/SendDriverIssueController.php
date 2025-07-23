<?php
namespace App\Http\Services\Driver\SendDriverIssue\Controller;

use App\Http\Services\Driver\SendDriverIssue\Logic\SendDriverIssueInput;
use App\Http\Services\Driver\SendDriverIssue\Logic\SendDriverIssueLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Driver\SendDriverIssue\Request\SendDriverIssueRequest;

class SendDriverIssueController extends Controller
{
    public function __invoke(SendDriverIssueRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new SendDriverIssueInput($request->validated());

        $service = new SendDriverIssueLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}