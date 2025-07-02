<?php
namespace App\Http\Services\Dashboard\VehicleManagement\DestroyVehicle\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class DestroyVehicleInput implements InputServiceInterface
{
    private $vehicleId;
    public function __construct( array $input)
    {
        $this->vehicleId = $input['vehicleId'];
    }
    // write your input function here..

    public function toArray(){
        return [
            ''=>''
        ];
    }



    /**
     * Get the value of vehicleId
     */
    public function getVehicleId() {
        return $this->vehicleId;
    }
}