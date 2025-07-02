<?php
namespace App\Http\Services\Driver\DeleteDriverAccount\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class DeleteDriverAccountInput implements InputServiceInterface
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