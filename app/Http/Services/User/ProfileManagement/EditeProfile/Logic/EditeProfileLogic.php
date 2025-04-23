<?php
namespace App\Http\Services\User\ProfileManagement\EditeProfile\Logic;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class EditeProfileLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private EditeProfileInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller();
    }


    public function execute (): ResponseModel {

        $userRepository = $this->repository->UserRepository();
        $user = $userRepository->updateRepository()->update([
            'id'=>$this->input->getUserId()]
            ,$this->input->toArray()
        );

        $response  = new EditeProfileOutput($user  , 'update successfully');
        return $response->send_as_object();
   }
}
