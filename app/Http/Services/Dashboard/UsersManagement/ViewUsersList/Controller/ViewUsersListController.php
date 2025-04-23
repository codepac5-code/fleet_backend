<?php
namespace App\Http\Services\Dashboard\UsersManagement\ViewUsersList\Controller;

use App\Http\Services\Dashboard\UsersManagement\ViewUsersList\Logic\ViewUsersListInput;
use App\Http\Services\Dashboard\UsersManagement\ViewUsersList\Logic\ViewUsersListLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use Illuminate\Http\Request;

class ViewUsersListController extends Controller
{
    public function __invoke(Request $request)
    {
        // validate input data and pass it to the service..
        $input = new ViewUsersListInput($request->all());

        $service = new ViewUsersListLogic($input); // call the service's logic

        // execute service and get result..
        return $service->execute(); // send response..
    }
}