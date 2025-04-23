<?php
namespace App\Http\Services\User\ProfileManagement\DeleteProfile\Controller;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Logic\Application\User\ProfileManagement\DeleteProfileService;
use App\Http\Services\User\ProfileManagement\DeleteProfile\Logic\DeleteProfileInput;
use App\Http\Services\User\ProfileManagement\DeleteProfile\Logic\DeleteProfileLogic;

class DeleteProfileController extends Controller
{
    public function __invoke(Request $request)
    {
        // validate input data and pass it to the service..
        $input = new DeleteProfileInput($request->validate());

        $service = new DeleteProfileLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}
