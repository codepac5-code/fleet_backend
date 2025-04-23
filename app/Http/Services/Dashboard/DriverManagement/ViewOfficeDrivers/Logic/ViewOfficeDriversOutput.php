<?php
namespace App\Http\Services\Dashboard\DriverManagement\ViewOfficeDrivers\Logic;

use App\Http\Core\InternalInterface\OutputServiceInterface;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class ViewOfficeDriversOutput implements OutputServiceInterface
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