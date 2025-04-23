<?php
namespace App\Http\Services\Dashboard\SubServiceManagement\BulkActionSubService\Logic;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class BulkActionSubServiceLogic implements Service {


    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private BulkActionSubServiceInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller();
    }
    

    public function execute (): ResponseModel {

        $ids = explode(',', $this->input->getRowIds());

        $actionType = $this->input->getType();

        $message = 'Bulk Action Updated';

        $update_repo = $this->repository
        ->SubServiceRepository()
        ->updateRepository();


        $delete_repo = $this->repository
        ->SubServiceRepository()
        ->deleteRepository();

        switch ($actionType) {
            case 'change-status':
                $update_repo->update_multiple_records_by_Key('id',$ids , ['status'=> $this->input->getStatus()]);
                $message = 'Bulk Sub Service Status Updated';
                break;
            
            case 'delete':
                $delete_repo->delete_multiple_records_by_Key('id',$ids);
                $message = 'Bulk Sub Service Deleted';
                break;
                
            case 'restore':
                $delete_repo->restor_multiple_records_by_Key('id', $ids);
                $message = 'Bulk Sub Service Restored';
                break;
                
            case 'permanently-delete':
                $delete_repo->force_delete_multiple_records_by_Key('id', $ids);
                $message = 'Bulk Sub Category Permanently Deleted';
                break;

                default:
                return response()->json(['status' => false ,'is_featured' => false, 'message' => 'Action Invalid']);
                break;
        }

        $response  = new BulkActionSubServiceOutput([] , $message);
        return $response->send_as_array();
        
   }



   
   public function action (){
    
     $sub_service  = $this->repository
    ->SubServiceRepository()
    ->readRepository()->get_first_with_trashed( ['id' , $this->input->getId()],
    $this->input->getType());

    //if($sub_service == null){make_exception()}

    $msg = __('messages.not_found_entry',['name' => __('messages.subservice')] );
    if( $this->input->getType() === 'restore') {
        $msg = __('messages.msg_restored',['name' => __('messages.subcategory')] );
    }
    
    if( $this->input->getType() === 'forcedelete'){
        $msg = __('messages.msg_forcedelete',['name' => __('messages.subcategory')] );
    }
    
    if(request()->is('api/*')){
        return comman_message_response($msg);
    }
    return comman_custom_response(['message'=> $msg , 'status' => true]);
   }
}