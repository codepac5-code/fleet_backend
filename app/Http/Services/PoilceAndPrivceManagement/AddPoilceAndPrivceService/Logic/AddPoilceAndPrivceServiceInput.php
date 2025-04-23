<?php
namespace App\Http\Services\PoilceAndPrivceManagement\AddPoilceAndPrivceService\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class AddPoilceAndPrivceServiceInput implements InputServiceInterface
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