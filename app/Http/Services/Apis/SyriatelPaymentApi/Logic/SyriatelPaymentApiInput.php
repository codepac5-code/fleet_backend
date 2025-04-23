<?php
namespace App\Http\Services\Apis\SyriatelPaymentApi\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class SyriatelPaymentApiInput implements InputServiceInterface
{

    private $orderId;
    private $userId;
    private $phoneNumber;
    private $amount;

    public function __construct( array $input)
    {
        $this->orderId      = $input['orderId'];
        $this->userId       = $input['userId'];
        $this->phoneNumber  = $input['phoneNumber'];
        $this->amount       = $input['amount'];

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
        return $this->amount;
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
}