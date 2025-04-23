<?php
namespace App\Http\Services\Dashboard\SlideManagement\EditeSlide\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class EditeSlideInput implements InputServiceInterface
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