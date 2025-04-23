<?php
namespace App\Http\Services\Dashboard\BookingManagement\Follow\FollowOrderOnMapToView\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class FollowOrderOnMapToViewInput implements InputServiceInterface
{

    private $orderId;

    public function __construct( array $input)
    {
        $this->orderId = $input['orderId'];
    }

    // write your input function here..

    public function toArray(){
        return [
            ''=>''
        ];
    }

    public function getOrderId(){
        return $this->orderId;
    }
}