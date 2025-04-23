<?php
namespace App\Http\Services\User\Auth\Login\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class LoginInput implements InputServiceInterface
{
    private string $phoneNumber;
    private string $password;


    public function __construct(  $input =[] ,
    ){
        $this->password     = $input['password'];
        $this->phoneNumber  = $input['phoneNumber'];
    }

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
