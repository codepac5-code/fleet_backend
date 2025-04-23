<?php
namespace App\Http\Services\Dashboard\SlideManagement\ViewSlide\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class ViewSlideInput implements InputServiceInterface
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