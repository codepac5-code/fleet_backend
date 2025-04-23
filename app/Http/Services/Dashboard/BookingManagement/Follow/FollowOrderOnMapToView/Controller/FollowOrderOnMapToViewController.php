<?php
namespace App\Http\Services\Dashboard\BookingManagement\Follow\FollowOrderOnMapToView\Controller;

use App\Http\Services\Dashboard\BookingManagement\Follow\FollowOrderOnMapToView\Logic\FollowOrderOnMapToViewInput;
use App\Http\Services\Dashboard\BookingManagement\Follow\FollowOrderOnMapToView\Logic\FollowOrderOnMapToViewLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\BookingManagement\Follow\FollowOrderOnMapToView\Request\FollowOrderOnMapToViewRequest;

class FollowOrderOnMapToViewController extends Controller
{
    public function __invoke(FollowOrderOnMapToViewRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new FollowOrderOnMapToViewInput($request->validated());

        $service = new FollowOrderOnMapToViewLogic($input); // call the service's logic

        // execute service and get result..
       return $service->execute();

    }
}