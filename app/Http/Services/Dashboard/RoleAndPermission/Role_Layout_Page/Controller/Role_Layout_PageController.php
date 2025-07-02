<?php
namespace App\Http\Services\Dashboard\RoleAndPermission\Role_Layout_Page\Controller;

use App\Http\Services\Dashboard\RoleAndPermission\Role_Layout_Page\Logic\Role_Layout_PageInput;
use App\Http\Services\Dashboard\RoleAndPermission\Role_Layout_Page\Logic\Role_Layout_PageLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\RoleAndPermission\Role_Layout_Page\Request\Role_Layout_PageRequest;

class Role_Layout_PageController extends Controller
{
    public function __invoke(Role_Layout_PageRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new Role_Layout_PageInput($request->validated());

        $service = new Role_Layout_PageLogic($input); // call the service's logic

        // execute service and get result..
        return $service->execute(); // send response..
    }
}