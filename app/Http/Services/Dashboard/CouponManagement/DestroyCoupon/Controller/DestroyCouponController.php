<?php
namespace App\Http\Services\Dashboard\CouponManagement\DestroyCoupon\Controller;

use App\Http\Services\Dashboard\CouponManagement\DestroyCoupon\Logic\DestroyCouponInput;
use App\Http\Services\Dashboard\CouponManagement\DestroyCoupon\Logic\DestroyCouponLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\CouponManagement\DestroyCoupon\Request\DestroyCouponRequest;

class DestroyCouponController extends Controller
{
    public function __invoke(DestroyCouponRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new DestroyCouponInput($request->validated());

        $service = new DestroyCouponLogic($input); // call the service's logic

        // execute service and get result..
        return $service->execute();

    }
}