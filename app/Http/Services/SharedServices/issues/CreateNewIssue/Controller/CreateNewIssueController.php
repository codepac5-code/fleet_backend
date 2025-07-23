<?php
namespace App\Http\Services\SharedServices\issues\CreateNewIssue\Controller;

use App\Http\Services\SharedServices\issues\CreateNewIssue\Logic\CreateNewIssueInput;
use App\Http\Services\SharedServices\issues\CreateNewIssue\Logic\CreateNewIssueLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\SharedServices\issues\CreateNewIssue\Request\CreateNewIssueRequest;

class CreateNewIssueController extends Controller
{
    public function __invoke(CreateNewIssueRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new CreateNewIssueInput($request->validated());

        $service = new CreateNewIssueLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}