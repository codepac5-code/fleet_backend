<?php
namespace App\Http\Services\Dashboard\HomeAdmin\Controller;

use App\Http\Services\Dashboard\HomeAdmin\Logic\HomeAdminInput;
use App\Http\Services\Dashboard\HomeAdmin\Logic\HomeAdminLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\HomeAdmin\Request\HomeAdminRequest;

class HomeAdminController extends Controller
{
    public function __invoke(HomeAdminRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new HomeAdminInput($request->validated());

        $service = new HomeAdminLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}