<?php
namespace App\Http\Services\Driver\Profile\EditImageProfile\Controller;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Driver\Profile\EditImageProfile\Logic\EditImageProfileInput;
use App\Http\Services\Driver\Profile\EditImageProfile\Logic\EditImageProfileLogic;
use App\Http\Services\Driver\Profile\EditImageProfile\Request\EditImageProfileRequest;

class EditImageProfileController extends Controller
{
    public function __invoke(EditImageProfileRequest $request)
    {
        // validate input data and pass it to the service..
        $data = $request->validated();
        $data['driverId'] = Auth::id();
        $input = new EditImageProfileInput($data);

        $service = new EditImageProfileLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}
