<?php
namespace App\Http\Services\Dashboard\RoleAndPermission\AddNewRole\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class AddNewRoleInput implements InputServiceInterface
{

    private $name;
    public function __construct( array $input)
    {
        $this->name = $input['name'];
    }

    // write your input function here..

    public function toArray(){
        return [
            ''=>''
        ];
    }

    /**
     * Get the value of name
     */
    public function getName() {
        return $this->name;
    }
}