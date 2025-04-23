<?php
namespace App\Http\Services\User\GetSubService\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\User\GetSubService\Logic\GetSubServiceInput;
use App\Http\Services\User\GetSubService\Logic\GetSubServiceLogic;
use App\Http\Services\User\GetSubService\Request\GetSubServiceRequest;

class GetSubServiceController extends Controller
{
    public function __invoke(GetSubServiceRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new GetSubServiceInput($request->validated());

        $service = new GetSubServiceLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}
