<?php
namespace App\Http\Services\Dashboard\UsersManagement\ViewUsersList\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class ViewUsersListInput implements InputServiceInterface
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