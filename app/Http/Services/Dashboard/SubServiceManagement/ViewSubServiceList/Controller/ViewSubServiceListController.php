<?php
namespace App\Http\Services\Dashboard\SubServiceManagement\ViewSubServiceList\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\SubServiceManagement\ViewSubServiceList\Logic\ViewSubServiceListInput;
use App\Http\Services\Dashboard\SubServiceManagement\ViewSubServiceList\Logic\ViewSubServiceListLogic;
use App\Http\Services\Dashboard\SubServiceManagement\ViewSubServiceList\Request\ViewSubServiceListRequest;

class ViewSubServiceListController extends Controller
{
    public function __invoke(ViewSubServiceListRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new ViewSubServiceListInput($request->validated());

        $service = new ViewSubServiceListLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return $result->getData();
       // return $result;

       return SendResponse::sendSuccessResponse($result); // send response..
    }
}
