<?php
namespace App\Http\Services\Driver\ChangeConnected\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class ChangeConnectedInput implements InputServiceInterface
{
    private bool $isConnected;
    private int $driverId;
    private $longitude;
    private $latitude;

    public function __construct( array $input)
    {
        $this->isConnected  = boolval($input['isConnected']);
        $this->driverId     = getAuthUser()->id;
        $this->latitude     = $input['driverLatitude'];
        $this->longitude    = $input['driverLongitude'];

    }

    // write your input function here..

    public function toArray(){
        return [];
    }

    /**
     * Get the value of isConnected
     */
    public function getIsConnected()
    {
        return $this->isConnected;
    }

    /**
     * Set the value of isConnected
     *
     * @return  self
     */
    public function setIsConnected($isConnected)
    {
        $this->isConnected = $isConnected;

        return $this;
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
     * Get the value of longitude
     */ 
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Set the value of longitude
     *
     * @return  self
     */ 
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Get the value of latitude
     */ 
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set the value of latitude
     *
     * @return  self
     */ 
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }
}
