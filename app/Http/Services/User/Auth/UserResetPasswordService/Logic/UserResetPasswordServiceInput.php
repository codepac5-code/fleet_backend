<?php
namespace App\Http\Services\User\Auth\UserResetPasswordService\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class UserResetPasswordServiceInput implements InputServiceInterface
{
    private string $password;
    private string $userId;
    private string $newPassword;

    public function __construct( Array $input){
        $this->password  = $input['password'];
        $this->newPassword  = $input['newPassword'];
        $this->userId  = $input['userId'];
    }

    // write your input function here..

    public function toArray(){
        return [
            ''=>''
        ];
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
     * Get the value of newPassword
     */
    public function getNewPassword()
    {
        return $this->newPassword;
    }

    /**
     * Set the value of newPassword
     *
     * @return  self
     */
    public function setNewPassword($newPassword)
    {
        $this->newPassword = $newPassword;

        return $this;
    }
}
