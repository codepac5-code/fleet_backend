<?php
namespace App\Http\Services\PoilceAndPrivceManagement\ShowPoilceAndPrivceService\Logic;

use App\Http\Core\Const\Messages\Attributes;
use App\Http\Core\Const\Messages\ErrorMessages;
use App\Http\Core\Const\Messages\SuccessMessages;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class ShowPoilceAndPrivceServiceLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private ShowPoilceAndPrivceServiceInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller();
    }


    public function execute (): ResponseModel {

        $readRepository =$this->repository->SubscriptionPolicyRepository()->readRepository();

        $policy =  $readRepository->getPolicyByLanguage();

        if($policy == null)
        {
            make_exception(ErrorMessages::getKey(ErrorMessages::$SomeThingWentWrong));
        }



        $response  = new ShowPoilceAndPrivceServiceOutput($policy  , SuccessMessages::getKey(''));
        return $response->send_as_object();
   }
}
