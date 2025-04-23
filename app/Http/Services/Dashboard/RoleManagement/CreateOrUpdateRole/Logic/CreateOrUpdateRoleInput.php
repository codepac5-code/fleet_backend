<?php
namespace App\Http\Services\Dashboard\RoleManagement\CreateOrUpdateRole\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class CreateOrUpdateRoleInput implements InputServiceInterface
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