<?php
namespace App\Http\Services\User\CompletedOrder\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class CompletedOrderInput implements InputServiceInterface
{
    private $orderId;
    private $driverId;
    private $userId;

    public function __construct( array $input)
    {
        $this->orderId  = $input['orderId'];
        $this->driverId = $input['driverId'];
        $this->userId   = $input['userId'];

    }

    // write your input function here..

    public function toArray(){
        return [
            ''=>''
        ];
    }

    /**
     * Get the value of driverId
     */ 
    public function getDriverId()
    {
        return $this->driverId;
    }

    /**
     * Set the value of driverId
     *
     * @return  self
     */ 
    public function setDriverId($driverId)
    {
        $this->driverId = $driverId;

        return $this;
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