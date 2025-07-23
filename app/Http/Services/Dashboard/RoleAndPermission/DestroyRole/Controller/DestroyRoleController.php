<?php
namespace App\Http\Services\Dashboard\RoleAndPermission\DestroyRole\Controller;

use App\Http\Services\Dashboard\RoleAndPermission\DestroyRole\Logic\DestroyRoleInput;
use App\Http\Services\Dashboard\RoleAndPermission\DestroyRole\Logic\DestroyRoleLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\RoleAndPermission\DestroyRole\Request\DestroyRoleRequest;

class DestroyRoleController extends Controller
{
    public function __invoke(DestroyRoleRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new DestroyRoleInput($request->validated());

        $service = new DestroyRoleLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}