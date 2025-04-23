<?php
namespace App\Http\Services\User\GetPaymentMethod\Logic;
use App\Http\Core\InternalInterface\InputServiceInterface;

class GetPaymentMethodInput implements InputServiceInterface
{
    private $wallet_charge = false;
    public function __construct( array $input)
    {
        $this->wallet_charge = $input['walletCharge'] ?? false;
    }

    public function isWalletCharge(){
        return $this->wallet_charge;
    }

    // write your input function here..

    public function toArray(){
        return [
            ''=>''
        ];
    }
}