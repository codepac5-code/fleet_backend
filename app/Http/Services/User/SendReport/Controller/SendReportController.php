<?php
namespace App\Http\Services\User\SendReport\Controller;

use App\Http\Services\User\SendReport\Logic\SendReportInput;
use App\Http\Services\User\SendReport\Logic\SendReportLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\User\SendReport\Request\SendReportRequest;

class SendReportController extends Controller
{
    public function __invoke(SendReportRequest $request)
    {

        // validate input data and pass it to the service..
        $input = new SendReportInput($request->validated());

        $service = new SendReportLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}