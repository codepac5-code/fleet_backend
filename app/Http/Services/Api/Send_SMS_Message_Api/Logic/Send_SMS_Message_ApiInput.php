<?php
namespace App\Http\Services\Api\Send_SMS_Message_Api\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class Send_SMS_Message_ApiInput implements InputServiceInterface
{
    private $phoneNumber;
    public function __construct( array $input)
    {
        $this->phoneNumber = $input['phoneNumber'];
    }

    // write your input function here..

    public function toArray(){
        return [
            ''=>''
        ];
    }

    /**
     * Get the value of phoneNumber
     */ 
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }
}