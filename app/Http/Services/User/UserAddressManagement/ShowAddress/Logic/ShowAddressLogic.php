<?php
namespace App\Http\Services\User\UserAddressManagement\ShowAddress\Logic;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class ShowAddressLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private ShowAddressInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel {

        // write your logic code..
        $addressReadRespository = $this->repository->AddressRepository()->readRepository();

        $address = $addressReadRespository->getByConditions(
            ["userId" => $this->input->getUserId()]
        );

        $response  = new ShowAddressOutput($address , '');
        return $response->send_as_object();
   }
}
