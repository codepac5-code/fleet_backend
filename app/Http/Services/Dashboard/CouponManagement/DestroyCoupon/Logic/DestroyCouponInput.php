<?php
namespace App\Http\Services\Dashboard\CouponManagement\DestroyCoupon\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class DestroyCouponInput implements InputServiceInterface
{
    private $id;
    public function __construct( array $input)
    {
        $this->id = $input['id'];
    }

    // write your input function here..

    public function toArray(){
        return [
            ''=>''
        ];
    }

    /**
     * Get the value of id
     */
    public function getId() {
        return $this->id;
    }
}