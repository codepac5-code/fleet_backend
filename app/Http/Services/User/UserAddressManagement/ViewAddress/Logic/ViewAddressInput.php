<?php
namespace App\Http\Services\User\UserAddressManagement\ViewAddress\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class ViewAddressInput implements InputServiceInterface
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
