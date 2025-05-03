<?php
namespace App\Http\Services\Driver\GetDriverWalletHistory\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class GetDriverWalletHistoryInput implements InputServiceInterface
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