<?php
namespace App\Http\Services\PoilceAndPrivceManagement\DeletePoilceAndPrivceService\Logic;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class DeletePoilceAndPrivceServiceLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private DeletePoilceAndPrivceServiceInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller();
    }


    public function execute (): ResponseModel {

        // write your logic code..

        $response  = new DeletePoilceAndPrivceServiceOutput([] , '');
        return $response->send_as_array();
   }
}
