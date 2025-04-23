<?php
namespace App\Http\Services\Dashboard\PublicServices\ChangeStatus\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class ChangeStatusInput implements InputServiceInterface
{

    private $id ;
    private $status ;

    public function __construct( array $input )
    {
        $this->id       = isset($input['id']) ? $input['id'] :  null;
        $this->status   = isset($input['status']) ? $input['status'] :null;
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

    /**
     * Get the value of status
     */ 
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set the value of status
     *
     * @return  self
     */ 
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }
}