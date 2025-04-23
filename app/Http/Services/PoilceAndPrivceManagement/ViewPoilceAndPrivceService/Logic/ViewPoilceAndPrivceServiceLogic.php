<?php
namespace App\Http\Services\PoilceAndPrivceManagement\ViewPoilceAndPrivceService\Logic;

use App\Http\Core\Const\Messages\ErrorMessages;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class ViewPoilceAndPrivceServiceLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private ViewPoilceAndPrivceServiceInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){}


    public function execute (): ResponseModel {

        $readRepository =$this->repository->SubscriptionPolicyRepository()->readRepository();

        $policy =  $readRepository->getPolicyByLanguage();

        if($policy == null)
        {make_exception(ErrorMessages::$SomeThingWentWrong);}


        $response  = new ViewPoilceAndPrivceServiceOutput($policy , '');
        return $response->send_as_object();
   }
}
