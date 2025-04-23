<?php
namespace App\Http\Services\Dashboard\Settings\GetOfficeComission\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class GetOfficeComissionInput implements InputServiceInterface
{
    private $officeId;
    public function __construct( array $input)
    {
        $this->officeId = $input['officeId'];
    }

    // write your input function here..

    public function toArray(){
        return [
            ''=>''
        ];
    }

    /**
     * Get the value of officeId
     */
    public function getOfficeId() {
        return $this->officeId;
    }

    /**
     * Set the value of officeId
     */
    public function setOfficeId($officeId): self {
        $this->officeId = $officeId;
        return $this;
    }
}