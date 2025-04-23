<?php
namespace App\Http\Services\Dashboard\OfficeManagement\DestroyOffice\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class DestroyOfficeInput implements InputServiceInterface
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