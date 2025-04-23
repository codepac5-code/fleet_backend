<?php
namespace App\Http\Services\Dashboard\VehicleManagement\Pages\CreateVehiclePage\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class CreateVehiclePageInput implements InputServiceInterface
{
    private $id;
    public function __construct( array $input)
    {
        $this->id = isset($input['id'])? $input['id'] : null;
    }

    // write your input function here..

    public function toArray(){
        return [
            ''=>''
        ];
    }

    /**
     * Get the value of id
     */ 
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @return  self
     */ 
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }
}