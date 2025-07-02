<?php
namespace App\Http\Services\Dashboard\Settings\SaveSettings\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class SaveSettingsInput implements InputServiceInterface
{
    private $request;
    public function __construct( array $input)
    {
        $this->request = $input;
    }

    // write your input function here..

    public function toArray(){
        return [
            ''=>''
        ];
    }

    /**
     * Get the value of request
     */
    public function getRequest() :array {
        return $this->request;
    }
}