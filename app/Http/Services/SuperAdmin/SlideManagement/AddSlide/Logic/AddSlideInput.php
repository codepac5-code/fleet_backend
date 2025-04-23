<?php
namespace App\Http\Services\Dashboard\SlideManagement\AddSlide\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class AddSlideInput implements InputServiceInterface
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