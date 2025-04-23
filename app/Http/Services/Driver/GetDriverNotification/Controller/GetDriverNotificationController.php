<?php
namespace App\Http\Services\Driver\GetDriverNotification\Controller;

use App\Http\Services\Driver\GetDriverNotification\Logic\GetDriverNotificationInput;
use App\Http\Services\Driver\GetDriverNotification\Logic\GetDriverNotificationLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Driver\GetDriverNotification\Request\GetDriverNotificationRequest;

class GetDriverNotificationController extends Controller
{
    public function __invoke(GetDriverNotificationRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new GetDriverNotificationInput($request->validated());

        $service = new GetDriverNotificationLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}