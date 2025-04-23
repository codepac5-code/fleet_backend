<?php
namespace App\Http\Services\Dashboard\SubServiceManagement\ViewSubServiceList\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class ViewSubServiceListInput implements InputServiceInterface
{


    private $filter;

    public function __construct( array $input)
    {
        $this->filter = isset($input['filter']) ? $input['filter'] : null;
    }

    // write your input function here..

    public function toArray(){
        return [
            ''=>''
        ];
    }

    /**
     * Get the value of filter
     */ 
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * Set the value of filter
     *
     * @return  self
     */ 
    public function setFilter($filter)
    {
        $this->filter = $filter;

        return $this;
    }
}