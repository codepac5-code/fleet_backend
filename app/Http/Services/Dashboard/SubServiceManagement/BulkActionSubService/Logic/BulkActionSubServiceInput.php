<?php
namespace App\Http\Services\Dashboard\SubServiceManagement\BulkActionSubService\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class BulkActionSubServiceInput implements InputServiceInterface
{

    public $id ;
    public $type;
    public $rowIds;
    public $status;
    
    public function __construct( array $input)
    {
        $this->id   = $input['id'];
        $this->type = $input['type'];
        $this->rowIds = $input['rowIds'];
        $this->status = $input['status'];
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
     * Get the value of type
     */ 
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the value of type
     *
     * @return  self
     */ 
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get the value of rowIds
     */ 
    public function getRowIds()
    {
        return $this->rowIds;
    }

    /**
     * Set the value of rowIds
     *
     * @return  self
     */ 
    public function setRowIds($rowIds)
    {
        $this->rowIds = $rowIds;

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