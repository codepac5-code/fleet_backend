<?php
namespace App\Http\Services\Dashboard\AddBalance\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class AddBalanceInput implements InputServiceInterface
{
    private $userType;
    private $userId;
    private $amount;
    
    public function __construct( array $input)
    {
        $this->userType = $input['userType'];
        $this->userId = $input['userId'];
        $this->amount = $input['amount'];

    }

    // write your input function here..

    public function toArray(){
        return [
            ''=>''
        ];
    }

    public function getUserType(){
        return $this->userType;
    }
    public function getUserId(){
        return $this->userId;
    }
    public function getAmount(){
        return $this->amount;
    }
}