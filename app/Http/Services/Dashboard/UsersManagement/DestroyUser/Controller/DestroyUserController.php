<?php
namespace App\Http\Services\Dashboard\UsersManagement\DestroyUser\Controller;

use App\Http\Services\Dashboard\UsersManagement\DestroyUser\Logic\DestroyUserInput;
use App\Http\Services\Dashboard\UsersManagement\DestroyUser\Logic\DestroyUserLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use Illuminate\Http\Request;

class DestroyUserController extends Controller
{
    public function __invoke(Request $request)
    {
        // validate input data and pass it to the service..
        $input = new DestroyUserInput($request->all());

        $service = new DestroyUserLogic($input); // call the service's logic

        // execute service and get result..
        return $service->execute(); // send response..
        
    }
}