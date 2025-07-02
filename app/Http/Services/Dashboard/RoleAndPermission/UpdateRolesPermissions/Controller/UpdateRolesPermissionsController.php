<?php
namespace App\Http\Services\Dashboard\RoleAndPermission\UpdateRolesPermissions\Controller;

use App\Http\Services\Dashboard\RoleAndPermission\UpdateRolesPermissions\Logic\UpdateRolesPermissionsInput;
use App\Http\Services\Dashboard\RoleAndPermission\UpdateRolesPermissions\Logic\UpdateRolesPermissionsLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\RoleAndPermission\UpdateRolesPermissions\Request\UpdateRolesPermissionsRequest;

class UpdateRolesPermissionsController extends Controller
{
    public function __invoke(UpdateRolesPermissionsRequest $request)
    {

        return $request->all();
        // validate input data and pass it to the service..
        $input = new UpdateRolesPermissionsInput($request->validated());

        $service = new UpdateRolesPermissionsLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}