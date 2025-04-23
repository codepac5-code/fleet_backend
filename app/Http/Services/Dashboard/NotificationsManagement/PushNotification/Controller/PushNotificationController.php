<?php
namespace App\Http\Services\Dashboard\NotificationsManagement\PushNotification\Controller;

use App\Http\Services\Dashboard\NotificationsManagement\PushNotification\Logic\PushNotificationInput;
use App\Http\Services\Dashboard\NotificationsManagement\PushNotification\Logic\PushNotificationLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\NotificationsManagement\PushNotification\Request\PushNotificationRequest;

class PushNotificationController extends Controller
{
    public function __invoke(PushNotificationRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new PushNotificationInput($request->validated());

        $service = new PushNotificationLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}