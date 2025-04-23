<?php

namespace App\Http\Services\Dashboard\Auth\Logout\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class LogoutInput implements InputServiceInterface
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
