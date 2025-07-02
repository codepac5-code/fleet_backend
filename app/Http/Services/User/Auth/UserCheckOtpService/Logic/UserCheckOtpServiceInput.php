<?php
namespace App\Http\Services\User\Auth\UserCheckOtpService\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class UserCheckOtpServiceInput implements InputServiceInterface
{
    private string $userId;
    private string $code;
    private string $phoneNumber;
    private string $referralCode;


    public function __construct( Array $input ,
    ){
        $this->userId    = $input['userId'];
        $this->code      = $input['code'];
        $this->phoneNumber      = $input['phoneNumber'];
        $this->referralCode     = $input['referralCode'] ?? null;
    }

    // write your input function here..

    public function toArray(){
        return [
            ''=>''
        ];
    }

    /**
     * Get the value of userId
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set the value of userId
     *
     * @return  self
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get the value of code
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set the value of code
     *
     * @return  self
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get the value of phoneNumber
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * Set the value of phoneNumber
     *
     * @return  self
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    /**
     * Get the value of referralCode
     *
     * @return string
     */
    public function getReferralCode(): string {
        return $this->referralCode;
    }
}
