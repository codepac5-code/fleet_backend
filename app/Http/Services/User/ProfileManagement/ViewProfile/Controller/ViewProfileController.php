<?php
namespace App\Http\Services\User\ProfileManagement\ViewProfile\Controller;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\User\ProfileManagement\ViewProfile\Logic\ViewProfileInput;
use App\Http\Services\User\ProfileManagement\ViewProfile\Logic\ViewProfileLogic;

class ViewProfileController extends Controller
{
    public function __invoke(Request $request)
    {
        // validate input data and pass it to the service..
        $input = new ViewProfileInput($request->validate());

        $service = new ViewProfileLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}
