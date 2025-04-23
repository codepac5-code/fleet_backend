<?php
namespace App\Http\Services\Dashboard\BookingManagement\AddBooking\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class AddBookingInput implements InputServiceInterface
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
