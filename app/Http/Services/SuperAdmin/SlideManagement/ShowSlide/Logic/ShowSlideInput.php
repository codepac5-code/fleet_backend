<?php
namespace App\Http\Services\Dashboard\SlideManagement\ShowSlide\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class ShowSlideInput implements InputServiceInterface
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