<?php
namespace App\Http\Services\Driver\Earning\Controller;

use App\Http\Services\Driver\Earning\Logic\EarningInput;
use App\Http\Services\Driver\Earning\Logic\EarningLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Driver\Earning\Request\EarningRequest;
use Illuminate\Support\Facades\Auth;

class EarningController extends Controller
{
    public function __invoke(EarningRequest $request)
    {
        // validate input data and pass it to the service..
        $data = $request->validated();
        $data['driverId'] = Auth::id();
        $input = new EarningInput($data);

        $service = new EarningLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}
