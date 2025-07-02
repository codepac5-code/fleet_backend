<?php
namespace App\Http\Services\Driver\ReceiveCompletedOrderInfo\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class ReceiveCompletedOrderInfoInput implements InputServiceInterface
{
    private $price;
    private $distance;
    private $time;
    private $orderId;
    public function __construct( array $input)
    {
        $this->orderId = $input['orderId'];
        $this->time = $input['time'];
        $this->price =$input['price'];
        $this->distance = $input['distances'];
    }

    // write your input function here..

    public function toArray(){
        return [
            ''=>''
        ];
    }

    /**
     * Get the value of price
     */
    public function getPrice() {
        return $this->price;
    }

    /**
     * Get the value of distance
     */
    public function getDistance() {
        return $this->distance;
    }

    /**
     * Get the value of time
     */
    public function getTime() {
        return $this->time;
    }

    /**
     * Get the value of orderId
     */
    public function getOrderId() {
        return $this->orderId;
    }
}