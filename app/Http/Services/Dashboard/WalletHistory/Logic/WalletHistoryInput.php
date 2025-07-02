<?php
namespace App\Http\Services\Dashboard\WalletHistory\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class WalletHistoryInput implements InputServiceInterface
{
    private $userType ;
    private $identifier;
    public function __construct( array $input)
    {
        $this->userType = $input['userType'];
        $this->identifier = $input['identifier'];
    }

    // write your input function here..

    public function toArray(){
        return [
            ''=>''
        ];
    }



    /**
     * Get the value of userType
     */ 
    public function getUserType()
    {
        return $this->userType;
    }

    /**
     * Get the value of identifier
     */
    public function getIdentifier() {
        return $this->identifier;
    }
}