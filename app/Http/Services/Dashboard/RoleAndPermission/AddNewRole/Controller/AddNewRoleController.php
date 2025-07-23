<?php
namespace App\Http\Services\Dashboard\RoleAndPermission\AddNewRole\Controller;

use App\Http\Services\Dashboard\RoleAndPermission\AddNewRole\Logic\AddNewRoleInput;
use App\Http\Services\Dashboard\RoleAndPermission\AddNewRole\Logic\AddNewRoleLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\RoleAndPermission\AddNewRole\Request\AddNewRoleRequest;

class AddNewRoleController extends Controller
{
    public function __invoke(AddNewRoleRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new AddNewRoleInput($request->validated());

        $service = new AddNewRoleLogic($input); // call the service's logic

        // execute service and get result..
       return $service->execute();

        // return SendResponse::sendSuccessResponse($result); // send response..
    }
}