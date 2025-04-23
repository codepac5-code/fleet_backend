<?php
namespace App\Http\Services\User\UserAddressManagement\AddAddress\Logic;

use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Services\User\UserAddressManagement\AddAddress\Logic\AddAddressOutput;



class AddAddressLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private AddAddressInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller();
    }


    public function execute (): ResponseModel {

        // write your logic code..
        $createAddressRepositry =$this->repository->AddressRepository()->createRepository();

        $createAddressRepositry->create([
            "userId" => $this->input->getUserId(),
            "addressName" => $this->input->getAddressName(),
            "address" => $this->input->getAddress(),
            "lat" => $this->input->getLatitude(),
            "lang" => $this->input->getLongitude(),
            "town" => $this->input->getTown(),
            "phone" => $this->input->getPhone(),
            "description" => $this->input->getDescription()
        ]);

        $response  = new AddAddressOutput([] , '');
        return $response->send_as_array();
   }
}
