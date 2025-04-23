<?php
namespace App\Http\Services\Dashboard\SubServiceManagement\BulkActionSubService\Controller;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\SubServiceManagement\BulkActionSubService\Logic\BulkActionSubServiceInput;
use App\Http\Services\Dashboard\SubServiceManagement\BulkActionSubService\Logic\BulkActionSubServiceLogic;
use App\Http\Services\Dashboard\SubServiceManagement\BulkActionSubService\Request\BulkActionSubServiceRequest;

class BulkActionSubServiceController extends Controller
{


    public function __invoke(BulkActionSubServiceRequest $request)
    {

        // validate input data and pass it to the service..
        $input = new BulkActionSubServiceInput($request->validated());

        $service = new BulkActionSubServiceLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }




    public function to_action(Request $request){

                // validate input data and pass it to the service..
                $input = new BulkActionSubServiceInput($request->all());

                $service = new BulkActionSubServiceLogic($input); // call the service's logic

                // execute service and get result..
                $result = $service->action();

                return $result; // send response..
    }



}
