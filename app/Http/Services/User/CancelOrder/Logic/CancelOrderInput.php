<?php
namespace App\Http\Services\User\CancelOrder\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class CancelOrderInput implements InputServiceInterface
{

    private $orderId;
    public function __construct( array $input)
    {
        $this->orderId = isset($input['orderId']) ?$input['orderId'] : null;
    }

    // write your input function here..


    public function toArray(){
        return [
            ''=>''
        ];
    }

    /**
     * Get the value of orderId
     */ 
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * Set the value of orderId
     *
     * @return  self
     */ 
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;

        return $this;
    }
}