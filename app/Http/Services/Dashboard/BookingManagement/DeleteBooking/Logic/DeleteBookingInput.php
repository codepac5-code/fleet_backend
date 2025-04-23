<?php

namespace App\Http\Services\Dashboard\BookingManagement\DeleteBooking\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class DeleteBookingInput implements InputServiceInterface
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
