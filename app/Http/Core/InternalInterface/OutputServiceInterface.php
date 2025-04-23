<?php
namespace App\Http\Core\InternalInterface;

use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

Interface OutputServiceInterface {

    public function send_as_array():ResponseModel ;
    public function send_as_object():ResponseModel ;

}
