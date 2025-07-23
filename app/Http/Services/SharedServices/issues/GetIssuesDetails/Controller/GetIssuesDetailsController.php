<?php
namespace App\Http\Services\SharedServices\issues\GetIssuesDetails\Controller;

use App\Http\Services\SharedServices\issues\GetIssuesDetails\Logic\GetIssuesDetailsInput;
use App\Http\Services\SharedServices\issues\GetIssuesDetails\Logic\GetIssuesDetailsLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\SharedServices\issues\GetIssuesDetails\Request\GetIssuesDetailsRequest;

class GetIssuesDetailsController extends Controller
{
    public function __invoke(GetIssuesDetailsRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new GetIssuesDetailsInput($request->validated());

        $service = new GetIssuesDetailsLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}