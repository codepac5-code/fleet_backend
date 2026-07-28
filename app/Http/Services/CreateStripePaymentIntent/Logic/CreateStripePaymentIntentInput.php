<?php
namespace App\Http\Services\CreateStripePaymentIntent\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class CreateStripePaymentIntentInput implements InputServiceInterface
{

    private $amount;
    private $orderId;
    public function __construct( array $input)
    {
        $this->amount = $input["amount"];
        $this->orderId = $input["orderId"] ?? null;
    }



    public function toArray(){
        return [
            ''=>''
        ];
    }

    /**
     * Get the value of amount
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Get the value of orderId
     */
    public function getOrderId()
    {
        return $this->orderId;
    }
}
