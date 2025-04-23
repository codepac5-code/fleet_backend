<?php
namespace App\Http\Services\PoilceAndPrivceManagement\ShowPoilceAndPrivceService\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class ShowPoilceAndPrivceServiceInput implements InputServiceInterface
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