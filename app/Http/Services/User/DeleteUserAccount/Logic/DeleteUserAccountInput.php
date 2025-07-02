<?php
namespace App\Http\Services\User\DeleteUserAccount\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class DeleteUserAccountInput implements InputServiceInterface
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