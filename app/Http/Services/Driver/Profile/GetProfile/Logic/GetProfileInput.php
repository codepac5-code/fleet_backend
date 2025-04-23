<?php
namespace App\Http\Services\Driver\Profile\GetProfile\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class GetProfileInput implements InputServiceInterface
{
    private  $driver;
    public function __construct(  $input)
    {
        $this->driver = $input;
    }

    // write your input function here..

    public function toArray(){
        return [

        ];
    }


    /**
     * Get the value of driver
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * Set the value of driver
     *
     * @return  self
     */
    public function setDriver($driver)
    {
        $this->driver = $driver;

        return $this;
    }
}
