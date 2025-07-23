<?php
namespace App\Http\Services\Driver\GetIssuesWithReplies\Controller;

use App\Http\Services\Driver\GetIssuesWithReplies\Logic\GetIssuesWithRepliesInput;
use App\Http\Services\Driver\GetIssuesWithReplies\Logic\GetIssuesWithRepliesLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Driver\GetIssuesWithReplies\Request\GetIssuesWithRepliesRequest;

class GetIssuesWithRepliesController extends Controller
{
    public function __invoke(GetIssuesWithRepliesRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new GetIssuesWithRepliesInput($request->validated());

        $service = new GetIssuesWithRepliesLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}