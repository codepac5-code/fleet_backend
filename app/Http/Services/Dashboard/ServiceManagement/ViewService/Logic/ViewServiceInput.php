<?php
namespace App\Http\Services\Dashboard\ServiceManagement\ViewService\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class ViewServiceInput implements InputServiceInterface
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
    public function getFilter() {
        return $this->filter;
    }
}