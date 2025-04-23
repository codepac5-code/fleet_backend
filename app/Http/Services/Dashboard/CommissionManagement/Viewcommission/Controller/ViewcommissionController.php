<?php
namespace App\Http\Services\Dashboard\commissionManagement\Viewcommission\Controller;

use App\Http\Services\Dashboard\commissionManagement\Viewcommission\Logic\ViewcommissionInput;
use App\Http\Services\Dashboard\commissionManagement\Viewcommission\Logic\ViewcommissionLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\commissionManagement\Viewcommission\Request\ViewcommissionRequest;

class ViewcommissionController extends Controller
{
    public function __invoke(ViewcommissionRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new ViewcommissionInput($request->validated());

        $service = new ViewcommissionLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}