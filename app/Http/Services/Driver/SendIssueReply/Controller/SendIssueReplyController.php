<?php
namespace App\Http\Services\Driver\SendIssueReply\Controller;

use App\Http\Services\Driver\SendIssueReply\Logic\SendIssueReplyInput;
use App\Http\Services\Driver\SendIssueReply\Logic\SendIssueReplyLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Driver\SendIssueReply\Request\SendIssueReplyRequest;

class SendIssueReplyController extends Controller
{
    public function __invoke(SendIssueReplyRequest $request)
    {

        // validate input data and pass it to the service..
        $input = new SendIssueReplyInput($request->validated());

        $service = new SendIssueReplyLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}