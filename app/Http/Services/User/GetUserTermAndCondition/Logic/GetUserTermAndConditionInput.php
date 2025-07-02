<?php
namespace App\Http\Services\User\GetUserTermAndCondition\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class GetUserTermAndConditionInput implements InputServiceInterface
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