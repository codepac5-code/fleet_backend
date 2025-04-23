<?php
namespace App\Http\Services\User\GetCopon\Controller;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\User\GetCopon\Logic\GetCoponInput;
use App\Http\Services\User\GetCopon\Logic\GetCoponLogic;
use App\Http\Services\User\GetCopon\Request\GetCoponRequest;

class GetCoponController extends Controller
{
    public function __invoke(GetCoponRequest $request)
    {
        $data = $request->validated();
        $data['userId'] = Auth::id();
        // validate input data and pass it to the service..
        $input = new GetCoponInput($data);

        $service = new GetCoponLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}
