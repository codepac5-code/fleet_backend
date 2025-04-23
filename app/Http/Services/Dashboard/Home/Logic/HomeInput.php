<?php
namespace App\Http\Services\Dashboard\Home\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class HomeInput implements InputServiceInterface
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