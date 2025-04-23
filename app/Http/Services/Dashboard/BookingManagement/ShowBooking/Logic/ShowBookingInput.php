<?php

namespace App\Http\Services\Dashboard\BookingManagement\ShowBooking\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class ShowBookingInput implements InputServiceInterface
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
