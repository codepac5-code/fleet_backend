<?php
namespace App\Http\Services\Dashboard\Auth\LoginToDashboard\Controller;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\Auth\LoginToDashboard\Logic\LoginToDashboardInput;
use App\Http\Services\Dashboard\Auth\LoginToDashboard\Logic\LoginToDashboardLogic;
use App\Http\Services\Dashboard\Auth\LoginToDashboard\Request\LoginToDashboardRequest;

class LoginToDashboardController extends Controller
{
    public function __invoke(LoginToDashboardRequest $request)
    {

    
        // validate input data and pass it to the service..
        $input = new LoginToDashboardInput( $request->validated());

        $service = new LoginToDashboardLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();//{$request->input('user_type')}();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}