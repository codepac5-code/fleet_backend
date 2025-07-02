<?php
namespace App\Http\Services\Dashboard\RolesManagement\ViewRolesList\Controller;

use App\Http\Services\Dashboard\RolesManagement\ViewRolesList\Logic\ViewRolesListInput;
use App\Http\Services\Dashboard\RolesManagement\ViewRolesList\Logic\ViewRolesListLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\RolesManagement\ViewRolesList\Request\ViewRolesListRequest;

class ViewRolesListController extends Controller
{
    public function __invoke(ViewRolesListRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new ViewRolesListInput($request->validated());

        $service = new ViewRolesListLogic($input); // call the service's logic

        // execute service and get result..
        return $service->execute();

    }
}