<?php
namespace App\Http\Services\Dashboard\VehicleManagement\BulkActionVehicle\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class BulkActionVehicleInput implements InputServiceInterface
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