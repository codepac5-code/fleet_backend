<?php
namespace App\Http\Services\User\ProfileManagement\AddProfile\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\User\ProfileManagement\AddProfile\Logic\AddProfileInput;
use App\Http\Services\User\ProfileManagement\AddProfile\Logic\AddProfileLogic;
use App\Http\Services\User\ProfileManagement\EditeProfile\Request\EditeProfileRequest;

class AddProfileController extends Controller
{
    public function __invoke(EditeProfileRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new AddProfileInput($request->validate());

        $service = new AddProfileLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}
