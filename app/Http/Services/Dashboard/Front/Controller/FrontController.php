<?php
namespace App\Http\Services\Dashboard\Front\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\Front\Logic\FrontInput;
use App\Http\Services\Dashboard\Front\Logic\FrontLogic;
use App\Http\Services\Dashboard\Front\Request\FrontRequest;

class FrontController extends Controller
{

     private $service;
    public function __construct()
    {
        $request = new FrontRequest;
        $input = new FrontInput($request->all());
        $this->service = new FrontLogic($input); // call the service's logic
    }
    
    // public function __invoke($request = new FrontRequest)
    // {
    //     $input = new FrontInput($request->all());
    //     $service = new FrontLogic($input); // call the service's logic
    //     // execute service and get result..
    //     return $result = $service->userLoginView();
    //     return SendResponse::sendSuccessResponse($result); // send response..
    // }

    public function userLoginView() {  return $this->service->userLoginView(); }

    

    
}