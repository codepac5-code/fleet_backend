<?php
namespace App\Http\Services\Dashboard\HelpDeskManagement\ViewHelpDesk\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class ViewHelpDeskInput implements InputServiceInterface
{

    private $filter;
    public function __construct( array $input)
    {
        $this->filter = $input['filter'];
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