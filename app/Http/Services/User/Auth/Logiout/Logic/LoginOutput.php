<?php
namespace App\Http\Services\User\Auth\Login\Logic;

use App\Http\Core\InternalInterface\OutputServiceInterface;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class LoginOutput implements OutputServiceInterface
{

    public function __construct(private $data , private string $message , private string $viewPath ='' ){}

    public function send_as_array(): ResponseModel {
        return (new ResponseModel(
            data:
            [
                'firstName'     => $this->data->firstName,
                'lastName'      => $this->data->lastName,
                'phoneNumber'   => $this->data->phoneNumber,
                'token'         => $this->data->token
            ],
            message:$this->message,
            status:200,
            viewPath:$this->viewPath
       ));
    }

    public function send_as_object():ResponseModel { return (new ResponseModel(
        data:$this->data,
        message:$this->message,
        status:200,
        viewPath:$this->viewPath
   ));
}



}
