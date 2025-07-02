<?php
namespace App\Http\Services\Dashboard\UsersManagement\DestroyUser\Logic;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use Illuminate\Http\JsonResponse;

class DestroyUserLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private DestroyUserInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse {

//    if(demoUserPermission()){
//             // if(request()->is('api/*')){
//             //     return comman_message_response( __('messages.demo_permission_denied') );
//             // }
//             return  redirect()->back()->withErrors(trans('messages.demo_permission_denied'));
//         }
        $user = $this->repository->UserRepository()->deleteRepository()->delete(['id' =>$this->input->getId()]);
        $msg = __('messages.msg_deleted',['name' => __('messages.user')] );

        if($user == false ) {
            $msg = __('messages.msg_fail_to_delete',['item' => __('messages.user')] );
        }
        return comman_custom_response(['message'=> $msg , 'status' => true]);
   }
}