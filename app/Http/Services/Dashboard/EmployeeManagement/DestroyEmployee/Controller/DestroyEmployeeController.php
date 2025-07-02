<?php
namespace App\Http\Services\Dashboard\EmployeeManagement\DestroyEmployee\Controller;

use App\Http\Services\Dashboard\EmployeeManagement\DestroyEmployee\Logic\DestroyEmployeeInput;
use App\Http\Services\Dashboard\EmployeeManagement\DestroyEmployee\Logic\DestroyEmployeeLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\EmployeeManagement\DestroyEmployee\Request\DestroyEmployeeRequest;

class DestroyEmployeeController extends Controller
{
    public function __invoke(DestroyEmployeeRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new DestroyEmployeeInput($request->validated());

        $service = new DestroyEmployeeLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}