<?php
namespace App\Http\Services\Driver\StartApplication\Controller;

use App\Http\Services\Driver\StartApplication\Logic\StartApplicationInput;
use App\Http\Services\Driver\StartApplication\Logic\StartApplicationLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Driver\StartApplication\Request\StartApplicationRequest;

class StartApplicationController extends Controller
{
    public function __invoke(StartApplicationRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new StartApplicationInput($request->validated());

        $service = new StartApplicationLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}