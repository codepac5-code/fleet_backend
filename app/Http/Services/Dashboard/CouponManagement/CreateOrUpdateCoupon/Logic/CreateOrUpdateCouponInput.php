<?php
namespace App\Http\Services\Dashboard\CouponManagement\CreateOrUpdateCoupon\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class CreateOrUpdateCouponInput implements InputServiceInterface
{

    private $id;
    private $code;
    private $discounType;
    private $discount;
    private $expireDate;
    private $serviceIds;
    private $isActive;
    private $limit;



    public function __construct( array $input)
    {
        $this->id          = $input['id'] ?? null;
        $this->code        = $input['code'];
        $this->discounType = $input['discounType'];
        $this->expireDate  = $input['expireDate'];
        $this->serviceIds  = $input['serviceIds'] ??[];
        $this->isActive    = $input['isActive'] ?? false;
        $this->limit       = $input['limit'] ?? false;

    }

    // write your input function here..

    public function toArray(){
        return [
            ''=>''
        ];
    }

    /**
     * Get the value of code
     */
    public function getCode() {
        return $this->code;
    }

    /**
     * Get the value of discounType
     */
    public function getDiscounType() {
        return $this->discounType;
    }

    /**
     * Get the value of discount
     */
    public function getDiscount() {
        return $this->discount;
    }

    /**
     * Get the value of expireDate
     */
    public function getExpireDate() {
        return $this->expireDate;
    }

    /**
     * Get the value of serviceIds
     */
    public function getServiceIds() {
        return $this->serviceIds;
    }

    /**
     * Get the value of isActive
     */
    public function getIsActive() {
        return $this->isActive;
    }

    /**
     * Get the value of id
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Get the value of limit
     */
    public function getLimit() {
        return $this->limit;
    }
}