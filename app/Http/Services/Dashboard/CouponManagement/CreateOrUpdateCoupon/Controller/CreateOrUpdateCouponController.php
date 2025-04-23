<?php
namespace App\Http\Services\Dashboard\CouponManagement\CreateOrUpdateCoupon\Controller;

use App\Http\Services\Dashboard\CouponManagement\CreateOrUpdateCoupon\Logic\CreateOrUpdateCouponInput;
use App\Http\Services\Dashboard\CouponManagement\CreateOrUpdateCoupon\Logic\CreateOrUpdateCouponLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\CouponManagement\CreateOrUpdateCoupon\Request\CreateOrUpdateCouponRequest;

class CreateOrUpdateCouponController extends Controller
{
    public function __invoke(CreateOrUpdateCouponRequest $request)
    {
return $request->all();
        // validate input data and pass it to the service..
        $input = new CreateOrUpdateCouponInput($request->validated());

        $service = new CreateOrUpdateCouponLogic($input); // call the service's logic

        // execute service and get result..
        return $service->execute(); // send response..
    }
}