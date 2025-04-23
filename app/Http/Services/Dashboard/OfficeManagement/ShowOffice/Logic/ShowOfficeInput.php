<?php
namespace App\Http\Services\Dashboard\OfficeManagement\ShowOffice\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class ShowOfficeInput implements InputServiceInterface
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