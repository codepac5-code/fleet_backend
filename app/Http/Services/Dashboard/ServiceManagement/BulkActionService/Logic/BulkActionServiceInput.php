<?php
namespace App\Http\Services\Dashboard\ServiceManagement\BulkActionService\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class BulkActionServiceInput implements InputServiceInterface
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