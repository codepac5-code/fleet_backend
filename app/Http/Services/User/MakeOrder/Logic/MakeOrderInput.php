<?php
namespace App\Http\Services\User\MakeOrder\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class MakeOrderInput implements InputServiceInterface
{

    private $startAddress;
    private $endAddress;
    private $durationDiff;
    private $startLatitude;
    private $startLongitude;
    private $endLatitude;
    private $endLongitude;
    private $distance;
    private $couponCode;
    private $subServiceId;
    private $userId;
    private $paymentId;
    private $totalAmount;
    private $amount;
    private $time;
    public $multiDestnationArray;

    public function __construct( array $input)
    {
        $this->startAddress     = $input['startAddress'];
        $this->endAddress       = $input['endAddress'];
        $this->startLatitude    = $input['startLatitude'];
        $this->startLongitude   = $input['startLongitude'];
        $this->endLatitude      = $input['endLatitude'];
        $this->endLongitude     = $input['endLongitude'];
        $this->distance         = $input['distance'];
        $this->couponCode       = $input['couponCode'];
        $this->subServiceId     = $input['subServiceId'];
        $this->paymentId        = $input['paymentId'];
        $this->totalAmount      = $input['totalAmount'];
        $this->userId           = $input['userId'];
        $this->amount           = $input['amount'];
        $this->time             = $input['durationDiff'];
        $this->multiDestnationArray           = $input['multiDestnationArray'];
    }

    public function bookingData() : Array{
        return [
            'startLongitude' => $this->getStartLongitude(),
            'endLongitude'   => $this->getEndLongitude(),
            'endAddress'     => $this->getEndAddress(),
            'startAddress'   => $this->getStartAddress(),
            'startLatitude'  => $this->getStartLatitude(),
            'endLatitude'    => $this->getEndLatitude(),
            'userId'         => $this->getUserId(),
            'distance'       => $this->getDistance(),
            'amount'         => $this->getAmount(),
            'paymentId'      => $this->getPaymentId(),
            'totalAmount'    => $this->getTotalAmount(),
            'subServiceId'   => $this->getSubServiceId(),
            'durationDiff'   => $this->getTime(),
            'multiDestnationArray'   => json_encode($this->multiDestnationArray),
        ];
    }
    // write your input function here..

    public function toArray(){
        return [
            ''=>''
        ];
    }

  
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Get the value of startAddress
     */ 
    public function getStartAddress()
    {
        return $this->startAddress;
    }

    /**
     * Set the value of startAddress
     *
     * @return  self
     */ 
    public function setStartAddress($startAddress)
    {
        $this->startAddress = $startAddress;

        return $this;
    }

    /**
     * Get the value of endAddress
     */ 
    public function getEndAddress()
    {
        return $this->endAddress;
    }

    /**
     * Set the value of endAddress
     *
     * @return  self
     */ 
    public function setEndAddress($endAddress)
    {
        $this->endAddress = $endAddress;

        return $this;
    }

    /**
     * Get the value of startLatitude
     */ 
    public function getStartLatitude()
    {
        return $this->startLatitude;
    }

    /**
     * Set the value of startLatitude
     *
     * @return  self
     */ 
    public function setStartLatitude($startLatitude)
    {
        $this->startLatitude = $startLatitude;

        return $this;
    }

    /**
     * Get the value of startLongitude
     */ 
    public function getStartLongitude()
    {
        return $this->startLongitude;
    }

    /**
     * Set the value of startLongitude
     *
     * @return  self
     */ 
    public function setStartLongitude($startLongitude)
    {
        $this->startLongitude = $startLongitude;

        return $this;
    }

    /**
     * Get the value of couponCode
     */ 
    public function getCouponCode()
    {
        return $this->couponCode;
    }

    /**
     * Set the value of couponCode
     *
     * @return  self
     */ 
    public function setCouponCode($couponCode)
    {
        $this->couponCode = $couponCode;

        return $this;
    }

    /**
     * Get the value of endLatitude
     */ 
    public function getEndLatitude()
    {
        return $this->endLatitude;
    }

    /**
     * Set the value of endLatitude
     *
     * @return  self
     */ 
    public function setEndLatitude($endLatitude)
    {
        $this->endLatitude = $endLatitude;

        return $this;
    }

    /**
     * Get the value of endLongitude
     */ 
    public function getEndLongitude()
    {
        return $this->endLongitude;
    }

    /**
     * Set the value of endLongitude
     *
     * @return  self
     */ 
    public function setEndLongitude($endLongitude)
    {
        $this->endLongitude = $endLongitude;

        return $this;
    }

    /**
     * Get the value of distance
     */ 
    public function getDistance()
    {
        return $this->distance;
    }

    /**
     * Set the value of distance
     *
     * @return  self
     */ 
    public function setDistance($distance)
    {
        $this->distance = $distance;

        return $this;
    }

    /**
     * Get the value of durationDiff
     */ 
    public function getDurationDiff()
    {
        return $this->durationDiff;
    }

    /**
     * Set the value of durationDiff
     *
     * @return  self
     */ 
    public function setDurationDiff($durationDiff)
    {
        $this->durationDiff = $durationDiff;

        return $this;
    }

    /**
     * Get the value of subServiceId
     */ 
    public function getSubServiceId()
    {
        return $this->subServiceId;
    }

    /**
     * Set the value of subServiceId
     *
     * @return  self
     */ 
    public function setSubServiceId($subServiceId)
    {
        $this->subServiceId = $subServiceId;

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
     * Get the value of paymentId
     */ 
    public function getPaymentId()
    {
        return $this->paymentId;
    }

    /**
     * Set the value of paymentId
     *
     * @return  self
     */ 
    public function setPaymentId($paymentId)
    {
        $this->paymentId = $paymentId;

        return $this;
    }

    /**
     * Get the value of totalAmount
     */ 
    public function getTotalAmount()
    {
        return $this->totalAmount;
    }

    /**
     * Set the value of totalAmount
     *
     * @return  self
     */ 
    public function setTotalAmount($totalAmount)
    {
        $this->totalAmount = $totalAmount;

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