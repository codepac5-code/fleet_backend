<?php
namespace App\Http\Services\Dashboard\SubServiceManagement\DestroySubService\Logic;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class DestroySubServiceLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private DestroySubServiceInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller();
    }


    public function execute (): ResponseModel {

        // if(demoUserPermission()){
        //     return  redirect()->back()->withErrors(trans('messages.demo_permission_denied'));
        // }

        $delete_repo = $this->repository->SubServiceRepository()->deleteRepository();

        $subservice = $delete_repo->delete(['id' =>$this->input->getId()]);

        $msg= __('messages.msg_fail_to_delete',['name' => __('messages.subservice')] );

        if(!$subservice) {
            $msg= __('messages.msg_deleted',['name' => __('messages.subservice')] );
        }
   
                //     return  redirect()->back()->withErrors(trans('messages.demo_permission_denied'));

        $response  = new DestroySubServiceOutput([] , $msg );
        return $response->send_as_array();
   }
}