<?php
namespace App\Http\Services\User\GetUserNotifications\Controller;

use App\Http\Services\User\GetUserNotifications\Logic\GetUserNotificationsInput;
use App\Http\Services\User\GetUserNotifications\Logic\GetUserNotificationsLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\User\GetUserNotifications\Request\GetUserNotificationsRequest;

class GetUserNotificationsController extends Controller
{
    public function __invoke(GetUserNotificationsRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new GetUserNotificationsInput($request->validated());

        $service = new GetUserNotificationsLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}