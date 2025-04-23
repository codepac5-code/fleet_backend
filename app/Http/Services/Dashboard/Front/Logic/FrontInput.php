<?php
namespace App\Http\Services\Dashboard\Front\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class FrontInput implements InputServiceInterface
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