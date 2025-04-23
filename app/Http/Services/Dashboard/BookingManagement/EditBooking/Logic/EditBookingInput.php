<?php

namespace App\Http\Services\Dashboard\BookingManagement\EditBooking\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class EditBookingInput implements InputServiceInterface
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
