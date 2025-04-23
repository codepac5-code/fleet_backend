<?php
namespace App\Http\Services\Dashboard\commissionManagement\Viewcommission\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class ViewcommissionInput implements InputServiceInterface
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