<?php
namespace App\Http\Services\Dashboard\Auth\AdminLogin\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\Auth\AdminLogin\Logic\AdminLoginInput;
use App\Http\Services\Dashboard\Auth\AdminLogin\Logic\AdminLoginLogic;
use App\Http\Services\Dashboard\Auth\AdminLogin\Request\AdminLoginRequest;

class AdminLoginController extends Controller
{
    public function __invoke(AdminLoginRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new AdminLoginInput($request->validated());

        $service = new AdminLoginLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}