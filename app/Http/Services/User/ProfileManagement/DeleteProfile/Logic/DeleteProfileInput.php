<?php
namespace App\Http\Services\User\ProfileManagement\DeleteProfile\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class DeleteProfileInput implements InputServiceInterface
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