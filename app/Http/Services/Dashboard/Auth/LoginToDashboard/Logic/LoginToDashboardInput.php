<?php
namespace App\Http\Services\Dashboard\Auth\LoginToDashboard\Logic;

use Illuminate\Support\Str;
use App\Http\Core\InternalInterface\InputServiceInterface;
use App\Http\Services\Dashboard\Auth\LoginToDashboard\Request\LoginToDashboardRequest;

class LoginToDashboardInput  implements InputServiceInterface 
{

    private $only;
    private $remember;
    private $guardName;
    private $email;
    private $password;
    

    public function __construct( array $input)
    {
        // $this->only        = $input['only'];
        $this->remember   = isset( $input['remember'])? $input['remember']:false;
        $this->password    = $input['password'];
        // $this->guardName   = $input['user_type'];
        $this->email       = $input['email'];
    }


    // public function throttleKey()
    // {
    //     return Str::lower($this->getEmail().'|'.$this->ip());
    // }
    // write your input function here..

    public function toArray():array{
        return [
            ''=>''
        ];
    }



    /**
     * Get the value of only
     */ 
    public function getOnly()
    {
        return $this->only;
    }

    /**
     * Set the value of only
     *
     * @return  self
     */ 
    public function setOnly($only)
    {
        $this->only = $only;

        return $this;
    }

    /**
     * Get the value of guardName
     */ 
    public function getGuardName()
    {
        return $this->guardName;
    }

    /**
     * Set the value of guardName
     *
     * @return  self
     */ 
    public function setGuardName($guardName)
    {
        $this->guardName = $guardName;

        return $this;
    }

    /**
     * Get the value of email
     */ 
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set the value of email
     *
     * @return  self
     */ 
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get the value of password
     */ 
    public function get_password()
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
     * Get the value of remember
     */ 
    public function getRemember()
    {
        return $this->remember;
    }

    /**
     * Set the value of remember
     *
     * @return  self
     */ 
    public function setRemember($remember)
    {
        $this->remember = $remember;

        return $this;
    }
}