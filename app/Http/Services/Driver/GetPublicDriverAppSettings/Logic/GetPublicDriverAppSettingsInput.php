<?php
namespace App\Http\Services\Driver\GetPublicDriverAppSettings\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class GetPublicDriverAppSettingsInput implements InputServiceInterface
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