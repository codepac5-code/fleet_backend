<?php
namespace App\Http\Services\Dashboard\BookingManagement\Follow\FollowOrderLayoutData\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class FollowOrderLayoutDataInput implements InputServiceInterface
{
    public function __construct( array $input)
    {}

    // write your input function here..

    public function toArray(){
        return [
            ''=>''
        ];
    }
}