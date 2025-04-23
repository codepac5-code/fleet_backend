<?php
namespace App\Http\Services\User\GetSlides\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class GetSlidesInput implements InputServiceInterface
{

    public function __construct( array $input)
    {
        
    }

    // write your input function here..

    public function toArray(){
        return [
            ''=>''
        ];
    }
}
