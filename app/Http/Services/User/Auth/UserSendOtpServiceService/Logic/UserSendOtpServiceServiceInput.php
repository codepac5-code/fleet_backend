<?php
namespace App\Http\Services\User\Auth\UserSendOtpServiceService\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class UserSendOtpServiceServiceInput implements InputServiceInterface
{
    private string $phoneNumber;

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
}
