<?php
namespace App\Http\Services\Dashboard\ServiceManagement\ActionService\Controller;

use App\Models\Service;
use Illuminate\Http\Request;
use App\Http\Response\SendResponse;
use App\Http\Controllers\Controller;
use App\Http\Services\Dashboard\ServiceManagement\ActionService\Logic\ActionServiceInput;
use App\Http\Services\Dashboard\ServiceManagement\ActionService\Logic\ActionServiceLogic;
use App\Http\Services\Dashboard\ServiceManagement\ActionService\Request\ActionServiceRequest;

class ActionServiceController extends Controller
{
    public function __invoke(ActionServiceRequest $request)
    {
        $id = $request->id;
        $service  = Service::withTrashed()->where('id',$id)->first();
        $msg = __('messages.t_found_entry',['name' => __('messages.service')] );
        if($request->type == 'restore') {
            $service->restore();
            $msg = __('messages.msg_restored',['name' => __('messages.service')] );
        }
        if($request->type === 'forcedelete'){
            $service->forceDelete();
            $msg = __('messages.msg_forcedelete',['name' => __('messages.service')] );
        }
        if(request()->is('api/*')){
            return comman_message_response($msg);
		}
        return comman_custom_response(['message'=> $msg , 'status' => true]);

        // // validate input data and pass it to the service..
        // $input = new ActionServiceInput($request->validated());

        // $service = new ActionServiceLogic($input); // call the service's logic

        // // execute service and get result..
        // $result = $service->execute();

        // return SendResponse::sendSuccessResponse($result); // send response..
    }



    public function removeFile(Request $request){

        $type = $request->type;
        $data =Service::find($request->id);
        if ($data != null) {
            $data->clearMediaCollection($type);
        }

        $message = __('messages.msg_removed', ['name' => __('messages.service')]);

        $response = [
            'status'    => true,
            'image'     => getSingleMedia($data, $type),
            'id'        => $request->id,
            'preview'   => $type . "_preview",
            'message'   => $message
        ];
        $message = __('messages.msg_removed', ['name' => __('messages.category')]);
        return comman_custom_response($response);

    }
}
