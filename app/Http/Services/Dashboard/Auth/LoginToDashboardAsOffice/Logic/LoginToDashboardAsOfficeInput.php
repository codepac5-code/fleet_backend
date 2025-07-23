<?php
namespace App\Http\Services\Dashboard\Auth\LoginToDashboardAsOffice\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;
use App\Http\Core\Const\Options\Guard;

class LoginToDashboardAsOfficeInput implements InputServiceInterface
{
    private $only;
    private $remember;
    private $guardName;
    private $email;
    private $password;
    private $role; 

    public function __construct(array $input)
    {
        $this->remember = isset($input['remember']) ? $input['remember'] : false;
        $this->password = $input['password'];
        $this->email    = $input['email'];
        $this->role     = $input['role'] ?? null; 

        if ($this->role === 'employee') {
            $this->guardName = Guard::$Employee;
        } elseif ($this->role === 'manager') {
            $this->guardName = Guard::$Office;
        } else {
            $this->guardName = null; 
        }
    }

    public function toArray(): array
    {
        return [
            'email'     => $this->email,
            'password'  => $this->password,
            'remember'  => $this->remember,
            'role'      => $this->role,
            'guardName' => $this->guardName,
        ];
    }

    public function getOnly()
    {
        return $this->only;
    }

    public function setOnly($only)
    {
        $this->only = $only;
        return $this;
    }

    public function getGuardName()
    {
        return $this->guardName;
    }

    public function setGuardName($guardName)
    {
        $this->guardName = $guardName;
        return $this;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    public function getRemember()
    {
        return $this->remember;
    }

    public function setRemember($remember)
    {
        $this->remember = $remember;
        return $this;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function setRole($role)
    {
        $this->role = $role;
        if ($role === 'employee') {
            $this->guardName = Guard::$Employee;
        } elseif ($role === 'manager') {
            $this->guardName = Guard::$Office;
        } else {
            $this->guardName = null;
        }
        return $this;
    }
}
