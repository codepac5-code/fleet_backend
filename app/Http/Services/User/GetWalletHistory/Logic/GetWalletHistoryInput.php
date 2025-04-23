<?php
namespace App\Http\Services\User\GetWalletHistory\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class GetWalletHistoryInput implements InputServiceInterface
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