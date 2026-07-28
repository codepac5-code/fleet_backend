<?php
namespace App\Http\Services\Dashboard\DriverManagement\DestroyDriver\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class DestroyDriverInput implements InputServiceInterface
{

    private $driverId;
    private $type;
    public function __construct( array $input)
    {
        $this->driverId = $input['id'];
        $this->type = $input['type'];
    }

    // write your input function here..

    public function toArray(){
        return [
            ''=>''
        ];
    }

    /**
     * Get the value of driverId
     */
    public function getDriverId()
    {
        return $this->driverId;
    }

    /**
     * Set the value of driverId
     *
     * @return  self
     */
    public function setDriverId($driverId)
    {
        $this->driverId = $driverId;

        return $this;
    }

    /**
     * Get the value of type
     */
    public function getType()
    {
        return $this->type;
    }
}
