<?php
namespace App\Http\Services\Apis\MTNPaymentApi\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class MTNPaymentApiInput implements InputServiceInterface
{

    private $phoneNumber ;
    private $amount;
    private $userId;
    private $orderId;
    public function __construct( array $input)
    {
        $this->phoneNumber  = $input['phoneNumber'];
        $this->amount       = $input['amount'];
        $this->userId       = $input['userId']??1;
        $this->orderId      = $input['orderId'] ?? 1;


    }

    // write your input function here..

    public function toArray(){
        return [
            ''=>''
        ];
    }

    /**
     * Get the value of phoneNumber
     */
    public function getPhoneNumber()
    {
        $phone = substr( $this->phoneNumber,1);
        info( $phone);
        $phone = '963'.$phone;
        return $phone;
    }

    /**
     * Set the value of phoneNumber
     *
     * @return  self
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    /**
     * Get the value of amount
     */
    public function getAmount()
    {
        return $this->amount  ;
    }

    /**
     * Set the value of amount
     *
     * @return  self
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get the value of userId
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set the value of userId
     *
     * @return  self
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

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
