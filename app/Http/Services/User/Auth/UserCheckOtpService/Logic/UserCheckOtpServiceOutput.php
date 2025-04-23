<?php
namespace App\Http\Services\User\Auth\UserCheckOtpService\Logic;

use App\Http\Core\InternalInterface\OutputServiceInterface;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class UserCheckOtpServiceOutput implements OutputServiceInterface
{

    public function __construct(private $data , private string $message , private string $viewPath ='' ){}

        public function send_as_array(): ResponseModel {
        return (new ResponseModel(
            data:[
                "phoneNumber"   => $this->data->phoneNumber,
                "userId"        => $this->data->id,
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
