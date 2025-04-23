<?php
namespace App\Http\Services\Dashboard\CouponManagement\ViewCouponsList\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class ViewCouponsListInput implements InputServiceInterface
{
    public function __construct( array $input)
    {}

    // write your input function here..

    public function toArray(){
        return [
            ''=>''
        ];
    }
}