<?php
namespace App\Http\Services\Dashboard\RoleAndPermission\UpdateRolesPermissions\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class UpdateRolesPermissionsInput implements InputServiceInterface
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