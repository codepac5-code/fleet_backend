<?php
namespace App\Http\Services\User\UserAddressManagement\DeleteAddress\Logic;

use App\Http\Core\Const\Messages\ErrorMessages;
use App\Http\Core\Const\Messages\SuccessMessages;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class DeleteAddressLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private DeleteAddressInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel {

        $addressReadReapository =$this->repository->AddressRepository()->readRepository();

        $address = $addressReadReapository->getByValue("id" , $this->input->getAddressId());

        if ($address->userId == $this->input->getUserId()) {

            if(!$address->delete()){
                make_exception(ErrorMessages::getKey(ErrorMessages::$SomeThingWentWrong));
            }
        }
        else {
            make_exception(ErrorMessages::getKey(ErrorMessages::$permsionDenied));
        }

        $response  = new DeleteAddressOutput([] , SuccessMessages::getKey(''));
        return $response->send_as_array();
   }
}
