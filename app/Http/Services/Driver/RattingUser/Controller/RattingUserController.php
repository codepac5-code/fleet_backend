<?php
namespace App\Http\Services\Driver\RattingUser\Controller;

use App\Http\Services\Driver\RattingUser\Logic\RattingUserInput;
use App\Http\Services\Driver\RattingUser\Logic\RattingUserLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Driver\RattingUser\Request\RattingUserRequest;

class RattingUserController extends Controller
{
    public function __invoke(RattingUserRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new RattingUserInput($request->validated());

        $service = new RattingUserLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}