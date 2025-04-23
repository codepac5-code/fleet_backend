<?php
namespace App\Http\Services\PoilceAndPrivceManagement\AddPoilceAndPrivceService\Logic;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class AddPoilceAndPrivceServiceLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private AddPoilceAndPrivceServiceInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){}


    public function execute (): ResponseModel {

        // write your logic code..

        $response  = new AddPoilceAndPrivceServiceOutput([] , '');
        return $response->send_as_array();
   }
}
