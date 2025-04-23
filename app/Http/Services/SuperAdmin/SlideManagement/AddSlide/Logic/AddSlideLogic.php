<?php
namespace App\Http\Services\Dashboard\SlideManagement\AddSlide\Logic;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class AddSlideLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private AddSlideInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller();

    }


    public function execute (): ResponseModel {

        // write your logic code..

        $response  = new AddSlideOutput([] , '');
        return $response->send_as_array();
   }
}
