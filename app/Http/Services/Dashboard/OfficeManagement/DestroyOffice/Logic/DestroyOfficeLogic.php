<?php
namespace App\Http\Services\Dashboard\OfficeManagement\DestroyOffice\Logic;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use Illuminate\Http\JsonResponse;

class DestroyOfficeLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private DestroyOfficeInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse{

        // if(demoUserPermission()){
        //     return  redirect()->back()->withErrors(trans('messages.demo_permission_denied'));
        // }

        $read_repo = $this->repository->OfficeRepository()->readRepository();

        $office =  $read_repo->find($id);
        $msg= __('messages.msg_fail_to_delete',['name' => __('messages.office')] );

        if($office != '') {
            $office->delete();
            $msg= __('messages.msg_deleted',['name' => __('messages.office')] );
        }
        if(request()->is('api/*')) {
            return comman_message_response($msg);
		}
        return comman_custom_response(['message'=> $msg, 'status' => true]);

        
        $response  = new DestroyOfficeOutput([] , '');
        return $response->send_as_array();
   }
}