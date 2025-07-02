<?php
namespace App\Http\Services\Api\Send_SMS_Message_Api\Logic;

use App\Http\Core\InternalInterface\OutputServiceInterface;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class Send_SMS_Message_ApiOutput implements OutputServiceInterface
{

    public function __construct(private $data , private string $message , private string $viewPath ='' ){}

        public function send_as_array(): ResponseModel {
        return (new ResponseModel(
            data:
            [
                ''
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