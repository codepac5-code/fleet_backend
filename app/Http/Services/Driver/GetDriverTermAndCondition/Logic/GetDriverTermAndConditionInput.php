<?php
namespace App\Http\Services\Driver\GetDriverTermAndCondition\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class GetDriverTermAndConditionInput implements InputServiceInterface
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