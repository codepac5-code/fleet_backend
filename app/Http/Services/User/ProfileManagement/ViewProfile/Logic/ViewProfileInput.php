<?php
namespace App\Http\Services\User\ProfileManagement\ViewProfile\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class ViewProfileInput implements InputServiceInterface
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