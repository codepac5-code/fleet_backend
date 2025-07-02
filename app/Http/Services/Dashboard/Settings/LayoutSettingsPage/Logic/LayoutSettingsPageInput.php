<?php
namespace App\Http\Services\Dashboard\Settings\LayoutSettingsPage\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class LayoutSettingsPageInput implements InputServiceInterface
{

    private $page;
    public function __construct( array $input)
    {
        $this->page = $input['page'];
    }


    public function toArray(){
        return [
            ''=>''
        ];
    }

    public function getPage(){
        return $this->page;
    }
}