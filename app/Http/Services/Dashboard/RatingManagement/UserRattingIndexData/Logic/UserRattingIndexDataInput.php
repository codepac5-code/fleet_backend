<?php
namespace App\Http\Services\Dashboard\RatingManagement\UserRattingIndexData\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class UserRattingIndexDataInput implements InputServiceInterface
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