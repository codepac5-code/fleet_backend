<?php
namespace App\Http\Services\Dashboard\OfficeManagement\AddBalanceToWallet\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class AddBalanceToWalletInput implements InputServiceInterface
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