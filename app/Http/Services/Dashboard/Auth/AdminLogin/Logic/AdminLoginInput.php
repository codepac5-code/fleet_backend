<?php
namespace App\Http\Services\Dashboard\Auth\AdminLogin\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class AdminLoginInput implements InputServiceInterface
{
    private string $email;
    private string $password;


    public function __construct(  $input =[] ,
    ){
        $this->password     = $input['password'];
        $this->email  = $input['email'];
    }

    // write your input function here..

    public function toArray(){
        return [
            ''=>''
        ];
    }
}
