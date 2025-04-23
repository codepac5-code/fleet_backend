<?php
namespace App\Http\Services\Dashboard\HomeAdmin\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class HomeAdminInput implements InputServiceInterface
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