<?php
namespace App\Http\Services\User\GetServices\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class GetServicesInput implements InputServiceInterface
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
