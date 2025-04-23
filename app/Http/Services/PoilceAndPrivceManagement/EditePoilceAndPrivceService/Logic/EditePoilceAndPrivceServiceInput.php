<?php
namespace App\Http\Services\PoilceAndPrivceManagement\EditePoilceAndPrivceService\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class EditePoilceAndPrivceServiceInput implements InputServiceInterface
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