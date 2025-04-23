<?php
namespace App\Http\Services\Dashboard\SubServiceManagement\DestroySubService\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\SubServiceManagement\DestroySubService\Logic\DestroySubServiceInput;
use App\Http\Services\Dashboard\SubServiceManagement\DestroySubService\Logic\DestroySubServiceLogic;
use App\Http\Services\Dashboard\SubServiceManagement\DestroySubService\Request\DestroySubServiceRequest;

class DestroySubServiceController extends Controller
{

    public function __invoke(DestroySubServiceRequest $request , $id)
    {
        // validate input data and pass it to the service..
        $input = new DestroySubServiceInput(        $request->validated());

        $service = new DestroySubServiceLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}
