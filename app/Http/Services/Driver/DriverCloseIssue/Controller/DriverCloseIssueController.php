<?php
namespace App\Http\Services\Driver\DriverCloseIssue\Controller;

use App\Http\Services\Driver\DriverCloseIssue\Logic\DriverCloseIssueInput;
use App\Http\Services\Driver\DriverCloseIssue\Logic\DriverCloseIssueLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Driver\DriverCloseIssue\Request\DriverCloseIssueRequest;

class DriverCloseIssueController extends Controller
{
    public function __invoke(DriverCloseIssueRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new DriverCloseIssueInput($request->validated());

        $service = new DriverCloseIssueLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}