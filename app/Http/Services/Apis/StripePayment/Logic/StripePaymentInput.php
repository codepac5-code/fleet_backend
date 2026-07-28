<?php
namespace App\Http\Services\Apis\StripePayment\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class StripePaymentInput implements InputServiceInterface
{
    private $amount;
    private $currency;
    private $orderId;
    private $payment_ID;
    public function __construct( array $input)
    {
        $this->amount = $input['amount'];
        $this->orderId = $input['orderId'];
    }

    // write your input function here..

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
     * Get the value of payment_ID
     */
    public function getPaymentID() {
        return $this->payment_ID;
    }


    /**
     * Get the value of orderId
     */
    public function getOrderId()
    {
        return $this->orderId;
    }
}
