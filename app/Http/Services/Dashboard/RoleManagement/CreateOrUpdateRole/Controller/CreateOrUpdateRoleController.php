<?php
namespace App\Http\Services\Dashboard\RoleManagement\CreateOrUpdateRole\Controller;

use App\Http\Services\Dashboard\RoleManagement\CreateOrUpdateRole\Logic\CreateOrUpdateRoleInput;
use App\Http\Services\Dashboard\RoleManagement\CreateOrUpdateRole\Logic\CreateOrUpdateRoleLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\RoleManagement\CreateOrUpdateRole\Request\CreateOrUpdateRoleRequest;

class CreateOrUpdateRoleController extends Controller
{
    public function __invoke(CreateOrUpdateRoleRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new CreateOrUpdateRoleInput($request->validated());

        $service = new CreateOrUpdateRoleLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}