<?php
namespace App\Http\Services\User\Auth\UserRegister\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\User\Auth\UserRegister\Logic\UserRegisterInput;
use App\Http\Services\User\Auth\UserRegister\Logic\UserRegisterLogic;
use App\Http\Services\User\Auth\UserRegister\Request\UserRegisterRequest;

class UserRegisterController extends Controller
{
    public function __invoke(UserRegisterRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new UserRegisterInput($request->validated());


        $service = new UserRegisterLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();


        return SendResponse::sendSuccessResponse($result); // send response..
    }
}
