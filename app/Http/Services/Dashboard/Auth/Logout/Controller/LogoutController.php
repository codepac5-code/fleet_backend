<?php
namespace App\Http\Services\Dashboard\Auth\Logout\Controller;

use App\Http\Services\Dashboard\Auth\Logout\Logic\LogoutInput;
use App\Http\Services\Dashboard\Auth\Logout\Logic\LogoutLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\Auth\Logout\Request\LogoutRequest;

class LogoutController extends Controller
{
    public function __invoke(LogoutRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new LogoutInput($request->validated());

        $service = new LogoutLogic($input); // call the service's logic

        // execute service and get result..
        return $service->execute();

    }
}