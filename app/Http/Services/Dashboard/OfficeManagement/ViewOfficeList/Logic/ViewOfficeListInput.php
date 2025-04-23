<?php
namespace App\Http\Services\Dashboard\OfficeManagement\ViewOfficeList\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class ViewOfficeListInput implements InputServiceInterface
{
    public function __construct( array $input)
    {}

    // write your input function here..

    public function toArray(){
        return [
            ''=>''
        ];
    }
}