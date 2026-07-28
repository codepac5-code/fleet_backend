<?php
namespace App\Http\Services\Dashboard\BookingManagement\AssignToDriver\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class AssignToDriverInput implements InputServiceInterface
{
    private $orderId;
    private $driverId;
    public function __construct( array $input)
    {
        $this->orderId = $input['orderId'] ?? null;
        $this->driverId = $input['driverId'] ?? null;
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
     * Get the value of driverId
     */
    public function getDriverId()
    {
        return $this->driverId;
    }
}
