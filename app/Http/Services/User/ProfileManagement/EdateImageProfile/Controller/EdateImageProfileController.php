<?php
namespace App\Http\Services\User\ProfileManagement\EdateImageProfile\Controller;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\User\ProfileManagement\EdateImageProfile\Logic\EdateImageProfileInput;
use App\Http\Services\User\ProfileManagement\EdateImageProfile\Logic\EdateImageProfileLogic;
use App\Http\Services\User\ProfileManagement\EdateImageProfile\Request\EditImageProfileRequest;

class EdateImageProfileController extends Controller
{
    public function __invoke(EditImageProfileRequest $request)
    {
        // validate input data and pass it to the service..
        $data = $request->validated();
        $data['userId'] = Auth::id();
        $input = new EdateImageProfileInput($data);

        $service = new EdateImageProfileLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}
