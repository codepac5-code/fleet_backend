<?php
namespace App\Http\Services\User\RattingDriver\Controller;

use App\Http\Services\User\RattingDriver\Logic\RattingDriverInput;
use App\Http\Services\User\RattingDriver\Logic\RattingDriverLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\User\RattingDriver\Request\RattingDriverRequest;

class RattingDriverController extends Controller
{
    public function __invoke(RattingDriverRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new RattingDriverInput($request->validated());

        $service = new RattingDriverLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}
