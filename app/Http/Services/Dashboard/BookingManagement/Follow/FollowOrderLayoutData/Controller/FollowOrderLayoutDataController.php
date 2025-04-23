<?php
namespace App\Http\Services\Dashboard\BookingManagement\Follow\FollowOrderLayoutData\Controller;

use App\Http\Services\Dashboard\BookingManagement\Follow\FollowOrderLayoutData\Logic\FollowOrderLayoutDataInput;
use App\Http\Services\Dashboard\BookingManagement\Follow\FollowOrderLayoutData\Logic\FollowOrderLayoutDataLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\BookingManagement\Follow\FollowOrderLayoutData\Request\FollowOrderLayoutDataRequest;

class FollowOrderLayoutDataController extends Controller
{
    public function __invoke(FollowOrderLayoutDataRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new FollowOrderLayoutDataInput($request->validated());

        $service = new FollowOrderLayoutDataLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}