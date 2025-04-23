<?php
namespace App\Http\Services\Dashboard\Transactions\ViewDriverPayout\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class ViewDriverPayoutInput implements InputServiceInterface
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