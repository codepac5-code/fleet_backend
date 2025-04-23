<?php
namespace App\Http\Services\PoilceAndPrivceManagement\AddPoilceAndPrivceService\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\PoilceAndPrivceManagement\AddPoilceAndPrivceService\Logic\AddPoilceAndPrivceServiceInput;
use App\Http\Services\PoilceAndPrivceManagement\AddPoilceAndPrivceService\Logic\AddPoilceAndPrivceServiceLogic;
use App\Http\Services\PoilceAndPrivceManagement\AddPoilceAndPrivceService\Request\AddPoilceAndPrivceServiceRequest;

class AddPoilceAndPrivceServiceController extends Controller
{
    public function __invoke(AddPoilceAndPrivceServiceRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new AddPoilceAndPrivceServiceInput($request->validate());

        $service = new AddPoilceAndPrivceServiceLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}