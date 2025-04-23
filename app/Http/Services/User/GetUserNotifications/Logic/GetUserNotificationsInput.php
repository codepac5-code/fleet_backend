<?php
namespace App\Http\Services\User\GetUserNotifications\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class GetUserNotificationsInput implements InputServiceInterface
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