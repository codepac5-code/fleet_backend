<?php
namespace App\Http\Services\Dashboard\Transactions\ViewOfficePayout\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class ViewOfficePayoutInput implements InputServiceInterface
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