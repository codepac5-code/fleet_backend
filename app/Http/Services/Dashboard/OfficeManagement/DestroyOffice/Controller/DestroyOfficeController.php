<?php
namespace App\Http\Services\Dashboard\OfficeManagement\DestroyOffice\Controller;

use App\Models\Office;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\OfficeManagement\DestroyOffice\Logic\DestroyOfficeInput;
use App\Http\Services\Dashboard\OfficeManagement\DestroyOffice\Logic\DestroyOfficeLogic;
use App\Http\Services\Dashboard\OfficeManagement\DestroyOffice\Request\DestroyOfficeRequest;

class DestroyOfficeController extends Controller
{
    public function __invoke(DestroyOfficeRequest $request , $id)
    {

        // if(demoUserPermission()){
        //     return  redirect()->back()->withErrors(trans('messages.demo_permission_denied'));
        // }
        $office = Office::find($id);
        $msg= __('messages.msg_fail_to_delete',['name' => __('messages.provider')] );

        if($office != null) {
            $office->delete();
            $msg= __('messages.msg_deleted',['name' => __('messages.provider')] );
        }
        if(request()->is('api/*')) {
            return comman_message_response($msg);
		}
        return comman_custom_response(['message'=> $msg, 'status' => true]);

        // validate input data and pass it to the service..
        $input = new DestroyOfficeInput($request->validated());

        $service = new DestroyOfficeLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}
