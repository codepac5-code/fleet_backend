<?php
namespace App\Http\Services\Dashboard\DriverManagement\DestroyDriver\Logic;
use Illuminate\Http\JsonResponse;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class DestroyDriverLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private DestroyDriverInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse{

        // if(demoUserPermission()){
        //     if(request()->is('api/*')){
        //         return comman_message_response( __('messages.demo_permission_denied') );
        //     }
        //     return  redirect()->back()->withErrors(trans('messages.demo_permission_denied'));
        // }
        $driver = $this->repository->DriverRepository()->deleteRepository()->delete(['id' =>$this->input->getDriverId()]);
        $msg = __('messages.msg_deleted',['name' => __('messages.driver')] );

        if($driver == false ) {
            $msg = __('messages.msg_fail_to_delete',['item' => __('messages.driver')] );
        }
        return comman_custom_response(['message'=> $msg , 'status' => true]);

   }
}