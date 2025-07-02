<?php
namespace App\Http\Services\Dashboard\EmployeeManagement\DestroyEmployee\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class DestroyEmployeeInput implements InputServiceInterface
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