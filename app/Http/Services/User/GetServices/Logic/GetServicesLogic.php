<?php
namespace App\Http\Services\User\GetServices\Logic;

use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Const\Messages\SuccessMessages;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class GetServicesLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private GetServicesInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel {


        $select = select_by_language([
            'id',
            'status',
            'description',
            'image',
            'title',
        ] , [
            'id',
            'status',
            'title_en as title',
            'description_en as description',
            'image',
        ]);

        $serviceReadRepository = $this->repository->ServiceRepository()->readRepository();
        $services = $serviceReadRepository->getByConditions(
            ['status'=>true] ,
            $select
        );

        $response  = new GetServicesOutput($services ,'get app services');
        return $response->send_as_object();
   }
}
