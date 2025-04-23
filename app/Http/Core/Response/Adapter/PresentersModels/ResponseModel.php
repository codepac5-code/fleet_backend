<?php
namespace App\Http\Core\Response\Adapter\PresentersModels;

use App\Http\Core\Const\Options\Redirect;

class ResponseModel
{
    public function __construct(private $data, private string  $message ,private $status = 404,
     private string $viewPath='' , private Redirect $redirect = Redirect::Back ){}

    function getData(){
        return $this->data;
    }

    function getMessage(){
        return $this->message;
    }

    function getStatus(){
        return $this->status;
    }
    public function getViewPath(){
        return $this->viewPath;
    }

    public function redirect(){
        return $this->redirect->value;
    }


    public function getExceptionAsArray(){
        return [
            'status'=>$this->getStatus(),
            'message' =>$this->getMessage(),
            'data'=>$this->getData()
        ];
    }
}
