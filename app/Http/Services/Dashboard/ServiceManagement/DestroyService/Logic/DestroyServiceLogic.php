<?php
namespace App\Http\Services\Dashboard\ServiceManagement\DestroyService\Logic;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use Illuminate\Http\JsonResponse;

class DestroyServiceLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private DestroyServiceInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }
    
    public function execute (): ResponseModel | JsonResponse {

        $banner = $this->repository->ServiceRepository()->deleteRepository()->delete(['id' =>$this->input->getId()]);
        
        $msg = __('messages.msg_deleted',['name' => __('messages.service')] );
       
        if($banner == false ) {
            $msg = __('messages.msg_fail_to_delete',['item' => __('messages.service')] );
            return comman_custom_response(['message'=> $msg , 'status' => false]);
        }
        return comman_custom_response(['message'=> $msg , 'status' => true]);
    }
        
}