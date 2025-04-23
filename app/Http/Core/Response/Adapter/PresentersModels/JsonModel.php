<?php
namespace App\Http\Core\Response\Adapter\PresentersModels;

class JsonModel
{
    public function __construct(private $data, private $message,private $status = 404 ){}

    function getData(){
        return $this->data;
    }

    function getMessage(){
        return $this->message;
    }

    function getStatus(){
        return $this->status;
    }
}
