<?php
namespace App\Http\Services\User\GetCopon\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class GetCoponInput implements InputServiceInterface
{
    private int $userId;
    private string $coponCode;
    private int $serviceId;
    private int $price;
    public function __construct( array $input)
    {
        $this->userId = $input['userId'];
        $this->coponCode = $input['couponCode'];
        $this->serviceId = $input['serviceId'];
        $this->price = $input['price'];
    }

    // write your input function here..

    public function toArray(){
        return [
            ''=>''
        ];
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
     * Get the value of coponCode
     */
    public function getCoponCode()
    {
        return $this->coponCode;
    }

    /**
     * Set the value of coponCode
     *
     * @return  self
     */
    public function setCoponCode($coponCode)
    {
        $this->coponCode = $coponCode;

        return $this;
    }

    /**
     * Get the value of serviceId
     */
    public function getServiceId()
    {
        return $this->serviceId;
    }

    /**
     * Set the value of serviceId
     *
     * @return  self
     */
    public function setServiceId($serviceId)
    {
        $this->serviceId = $serviceId;

        return $this;
    }

    /**
     * Get the value of price
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set the value of price
     *
     * @return  self
     */
    public function setPrice($price)
    {
        $this->price = $price;
        return $this;
    }
}
