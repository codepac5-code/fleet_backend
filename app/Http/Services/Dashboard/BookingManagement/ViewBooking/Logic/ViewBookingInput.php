<?php
namespace App\Http\Services\Dashboard\BookingManagement\ViewBooking\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class ViewBookingInput implements InputServiceInterface
{
    private $filter;
    private $pageType;
    public function __construct( array $input)
    {
        $this->filter = isset($input['filter']) ? $input['filter'] : null;
        $this->pageType = $input['type'] ?? 'none';
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

    /**
     * Get the value of pageType
     */
    public function getPageType() {
        return $this->pageType;
    }
}