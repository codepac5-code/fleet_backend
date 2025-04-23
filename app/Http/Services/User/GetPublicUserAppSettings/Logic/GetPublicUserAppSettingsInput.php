<?php
namespace App\Http\Services\User\GetPublicUserAppSettings\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class GetPublicUserAppSettingsInput implements InputServiceInterface
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