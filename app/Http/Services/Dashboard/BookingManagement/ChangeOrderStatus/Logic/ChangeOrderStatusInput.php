<?php
namespace App\Http\Services\Dashboard\BookingManagement\ChangeOrderStatus\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class ChangeOrderStatusInput implements InputServiceInterface
{


    private $status;
    private $reason;
    private $orderId;

    public function __construct( array $input)
    {
        $this->reason   = $input['reason'] ?? null;
        $this->orderId  = $input['orderId'];
        $this->status   = $input['status'];
    }


    public function toArray(){
        return [
            ''=>''
        ];
    }

    /**
     * Get the value of orderId
     */
    public function getOrderId() {
        return $this->orderId;
    }

    /**
     * Get the value of reason
     */
    public function getReason() {
        return $this->reason;
    }

    /**
     * Get the value of status
     */
    public function getStatus() {
        return $this->status;
    }
}