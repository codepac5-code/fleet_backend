<?php
namespace App\Http\Services\User\UserAddressManagement\EditeAddress\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class EditeAddressInput implements InputServiceInterface
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
