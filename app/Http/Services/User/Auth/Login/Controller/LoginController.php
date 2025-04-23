<?php
namespace App\Http\Services\User\Auth\Login\Controller;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\User\Auth\Login\Logic\LoginInput;
use App\Http\Services\User\Auth\Login\Logic\LoginLogic;
use App\Http\Services\User\Auth\Login\Request\LoginRequest;

class LoginController extends Controller
{
    public function __invoke(LoginRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new LoginInput($request->validated());

        $service = new LoginLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}
