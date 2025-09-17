<?php
namespace App\Http\Services\Driver\StartApplication\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class StartApplicationInput implements InputServiceInterface
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