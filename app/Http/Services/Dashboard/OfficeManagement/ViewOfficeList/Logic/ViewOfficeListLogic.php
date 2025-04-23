<?php
namespace App\Http\Services\Dashboard\OfficeManagement\ViewOfficeList\Logic;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class ViewOfficeListLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct (
    //---------------------------------------------------------------------------------------
    private ViewOfficeListInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel {

        // write your logic code..

        $response  = new ViewOfficeListOutput([] , '');
        return $response->send_as_array();
   }
}