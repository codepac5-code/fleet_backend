<?php
namespace App\Http\Services\User\InquireUserOrderStatus\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class InquireUserOrderStatusInput implements InputServiceInterface
{

    private $orderId;
    public function __construct( array $input)
    {
        $this->orderId = $input['orderId'];
    }

        /**
     * Get the value of orderId
     */ 
    public function getOrderId()
    {
        return $this->orderId;
    }

    // write your input function here..

    public function toArray(){
        return [
            ''=>''
        ];
    }
}