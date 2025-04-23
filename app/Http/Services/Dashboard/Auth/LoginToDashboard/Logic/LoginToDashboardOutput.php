<?php
namespace App\Http\Services\Dashboard\Auth\LoginToDashboard\Logic;

use App\Http\Core\Const\Options\Redirect;
use App\Http\Core\InternalInterface\OutputServiceInterface;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class LoginToDashboardOutput implements OutputServiceInterface
{

    public function __construct(private $data , private string $message , private string $viewPath ='' , private Redirect $redirect = Redirect::Back){}

        public function send_as_array(): ResponseModel {
        return (new ResponseModel(
            data:
            [
                ''
            ],
            message:$this->message,
            status:200,
            viewPath:$this->viewPath,
            redirect: $this->redirect
       ));
    }

    public function send_as_object():ResponseModel { return (new ResponseModel(
        data:$this->data,
        message:$this->message,
        status:200,
        viewPath:$this->viewPath,
        redirect: $this->redirect
    ));
}

}