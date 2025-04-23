<?php
namespace App\Http\Services\Dashboard\BookingManagement\BookingStatusUpdate\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class BookingStatusUpdateInput implements InputServiceInterface
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