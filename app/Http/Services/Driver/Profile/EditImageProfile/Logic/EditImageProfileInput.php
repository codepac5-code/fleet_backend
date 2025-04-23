<?php
namespace App\Http\Services\Driver\Profile\EditImageProfile\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class EditImageProfileInput implements InputServiceInterface
{

    private  $photo;
    private int $driverId;

    public function __construct($input){
        $this->photo = $input["photo"];
        $this->driverId = $input['driverId'];
    }


    public function toArray(){
        return
        [
            'id' =>$this->driverId,
            'photo'  =>$this->photo,
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
     * Get the value of photo
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * Set the value of photo
     *
     * @return  self
     */
    public function setPhoto($photo)
    {
        $this->photo = $photo;

        return $this;
    }
}
