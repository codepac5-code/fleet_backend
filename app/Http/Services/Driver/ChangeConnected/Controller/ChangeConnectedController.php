<?php
namespace App\Http\Services\Driver\ChangeConnected\Controller;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Driver\ChangeConnected\Logic\ChangeConnectedInput;
use App\Http\Services\Driver\ChangeConnected\Logic\ChangeConnectedLogic;
use App\Http\Services\Driver\ChangeConnected\Request\ChangeConnectedRequest;

class ChangeConnectedController extends Controller
{
    public function __invoke(ChangeConnectedRequest $request)
    {
        // validate input data and pass it to the service..
        $data = $request->validated();
        $data['driverId'] = Auth::id();
        $input = new ChangeConnectedInput($data);

        $service = new ChangeConnectedLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}
