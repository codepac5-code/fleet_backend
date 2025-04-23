<?php
namespace App\Http\Services\FleetWalletPayment\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class FleetWalletPaymentInput implements InputServiceInterface
{

    private $amount;
    private $userId;
    private $orderId;

    public function __construct( array $input)
    {
        $this->amount   = $input['amount'];
        $this->userId   = $input['userId'];
        $this->orderId = $input['orderId'];
    }

    // write your input function here..

    public function toArray(){
        return [
            ''=>''
        ];
    }

    public function getAmount(){
        return $this->amount;
    }

    public function getUserId(){
        return $this->userId;
    }

    public function getOrderId(){
        return $this->orderId;
    }


}