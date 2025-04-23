<?php
namespace App\Http\Services\Dashboard\CouponManagement\ViewCouponsList\Controller;

use App\Http\Services\Dashboard\CouponManagement\ViewCouponsList\Logic\ViewCouponsListInput;
use App\Http\Services\Dashboard\CouponManagement\ViewCouponsList\Logic\ViewCouponsListLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\CouponManagement\ViewCouponsList\Request\ViewCouponsListRequest;

class ViewCouponsListController extends Controller
{
    public function __invoke(ViewCouponsListRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new ViewCouponsListInput($request->validated());

        $service = new ViewCouponsListLogic($input); // call the service's logic

        // execute service and get result..
        return $service->execute(); // send response..
    }
}