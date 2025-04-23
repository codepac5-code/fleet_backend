<?php
namespace App\Http\Services\PoilceAndPrivceManagement\ViewPoilceAndPrivceService\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class ViewPoilceAndPrivceServiceInput implements InputServiceInterface
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