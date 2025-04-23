<?php
namespace App\Http\Services\User\ProfileManagement\EditeProfile\Controller;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\User\ProfileManagement\EditeProfile\Logic\EditeProfileInput;
use App\Http\Services\User\ProfileManagement\EditeProfile\Logic\EditeProfileLogic;
use App\Http\Services\User\ProfileManagement\EditeProfile\Request\EditeProfileRequest;

class EditeProfileController extends Controller
{
    public function __invoke(EditeProfileRequest $request)
    {
        // validate input data and pass it to the service..
        $data = $request->validated();
        $data["userId"] = Auth::id();

        $input = new EditeProfileInput($data);

        $service = new EditeProfileLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}
