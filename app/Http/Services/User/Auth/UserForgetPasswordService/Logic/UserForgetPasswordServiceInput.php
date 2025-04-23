<?php
namespace App\Http\Services\User\Auth\UserForgetPasswordService\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class UserForgetPasswordServiceInput implements InputServiceInterface
{
    private string $password;
    private string $code;
    private string $phoneNumber;

    public function __construct( array $input)
    {
        $this->password  = $input['password'];
        $this->code  = $input['code'];
        $this->phoneNumber  = $input['phoneNumber'];
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
     * Get the value of password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set the value of password
     *
     * @return  self
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }
}
