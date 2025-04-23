<?php
namespace App\Http\Services\Driver\GetDriverNotification\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class GetDriverNotificationInput implements InputServiceInterface
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