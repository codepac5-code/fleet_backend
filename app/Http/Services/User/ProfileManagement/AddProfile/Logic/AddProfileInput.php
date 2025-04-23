<?php
namespace App\Http\Services\User\ProfileManagement\AddProfile\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class AddProfileInput implements InputServiceInterface
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