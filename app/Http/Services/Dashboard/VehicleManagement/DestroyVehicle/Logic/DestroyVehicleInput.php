<?php
namespace App\Http\Services\Dashboard\VehicleManagement\DestroyVehicle\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class DestroyVehicleInput implements InputServiceInterface
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