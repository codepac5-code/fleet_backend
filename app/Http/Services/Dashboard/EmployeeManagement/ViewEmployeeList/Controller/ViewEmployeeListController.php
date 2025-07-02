<?php
namespace App\Http\Services\Dashboard\EmployeeManagement\ViewEmployeeList\Controller;

use App\Http\Services\Dashboard\EmployeeManagement\ViewEmployeeList\Logic\ViewEmployeeListInput;
use App\Http\Services\Dashboard\EmployeeManagement\ViewEmployeeList\Logic\ViewEmployeeListLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\EmployeeManagement\ViewEmployeeList\Request\ViewEmployeeListRequest;

class ViewEmployeeListController extends Controller
{
    public function __invoke(ViewEmployeeListRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new ViewEmployeeListInput($request->validated());

        $service = new ViewEmployeeListLogic($input); // call the service's logic

        // execute service and get result..
        return $service->execute();

        // return SendResponse::sendSuccessResponse($result); // send response..
    }
}