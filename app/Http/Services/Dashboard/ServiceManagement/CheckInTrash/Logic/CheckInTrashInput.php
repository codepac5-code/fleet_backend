<?php
namespace App\Http\Services\Dashboard\ServiceManagement\CheckInTrash\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class CheckInTrashInput implements InputServiceInterface
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