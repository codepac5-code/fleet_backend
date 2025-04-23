<?php
namespace App\Http\Services\Dashboard\PublicServices\ChangeStatus\Controller;

use App\Models\Service;
use App\Models\SubService;
use App\Http\Controllers\Controller;
use App\Http\Services\Dashboard\PublicServices\ChangeStatus\Logic\ChangeStatusInput;
use App\Http\Services\Dashboard\PublicServices\ChangeStatus\Logic\ChangeStatusLogic;
use App\Http\Services\Dashboard\PublicServices\ChangeStatus\Request\ChangeStatusRequest;

class ChangeStatusController extends Controller
{
    public function __invoke(ChangeStatusRequest $request , $entity_type)
    {

        //return $request->input('status');
        // validate input data and pass it to the service..
        $input = new ChangeStatusInput($request->validated());

        $service = new ChangeStatusLogic($input); // call the service's logic
        
    //    execute service and get result..
       $result = $service->{$entity_type}();

        // if ( !($result instanceof ResponseModel) ){
        //     return $result;
        // }

        return $result;
        // return SendResponse::sendSuccessResponse($result); // send response..
    }
}