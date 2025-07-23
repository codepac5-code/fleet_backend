<?php
namespace App\Http\Services\Dashboard\RoleAndPermission\DestroyRole\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class DestroyRoleInput implements InputServiceInterface
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