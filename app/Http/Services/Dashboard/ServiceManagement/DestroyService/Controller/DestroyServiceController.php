<?php
namespace App\Http\Services\Dashboard\ServiceManagement\DestroyService\Controller;

use App\Http\Services\Dashboard\ServiceManagement\DestroyService\Logic\DestroyServiceInput;
use App\Http\Services\Dashboard\ServiceManagement\DestroyService\Logic\DestroyServiceLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\ServiceManagement\DestroyService\Request\DestroyServiceRequest;

class DestroyServiceController extends Controller
{
    public function __invoke(DestroyServiceRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new DestroyServiceInput($request->all());

        $service = new DestroyServiceLogic($input); // call the service's logic

        // execute service and get result..
        return $service->execute();

        //return SendResponse::sendSuccessResponse($result); // send response..
    }
}