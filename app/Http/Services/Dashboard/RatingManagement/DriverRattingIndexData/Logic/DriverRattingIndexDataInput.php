<?php
namespace App\Http\Services\Dashboard\RatingManagement\DriverRattingIndexData\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class DriverRattingIndexDataInput implements InputServiceInterface
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