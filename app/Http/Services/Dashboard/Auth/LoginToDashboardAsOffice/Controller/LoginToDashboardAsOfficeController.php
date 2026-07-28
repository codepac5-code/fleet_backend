<?php
namespace App\Http\Services\Dashboard\Auth\LoginToDashboardAsOffice\Controller;

use App\Http\Services\Dashboard\Auth\LoginToDashboardAsOffice\Logic\LoginToDashboardAsOfficeInput;
use App\Http\Services\Dashboard\Auth\LoginToDashboardAsOffice\Logic\LoginToDashboardAsOfficeLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\Auth\LoginToDashboardAsOffice\Request\LoginToDashboardAsOfficeRequest;
use Illuminate\Support\Facades\Auth;

class LoginToDashboardAsOfficeController extends Controller
{
    public function __invoke(LoginToDashboardAsOfficeRequest $request)
    {
        
        // validate input data and pass it to the service..
        $input = new LoginToDashboardAsOfficeInput($request->validated());

        $service = new LoginToDashboardAsOfficeLogic($input); // call the service's logic

        // execute service and get result..
        return $service->execute();// send response..

    }
}
