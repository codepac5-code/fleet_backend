<?php
namespace App\Http\Services\PoilceAndPrivceManagement\DeletePoilceAndPrivceService\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class DeletePoilceAndPrivceServiceInput implements InputServiceInterface
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