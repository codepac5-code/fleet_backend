<?php
namespace App\Http\Services\SharedServices\issues\CloeIssue\Controller;

use App\Http\Services\SharedServices\issues\CloeIssue\Logic\CloeIssueInput;
use App\Http\Services\SharedServices\issues\CloeIssue\Logic\CloeIssueLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\SharedServices\issues\CloeIssue\Request\CloeIssueRequest;

class CloeIssueController extends Controller
{
    public function __invoke(CloeIssueRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new CloeIssueInput($request->validated());

        $service = new CloeIssueLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}