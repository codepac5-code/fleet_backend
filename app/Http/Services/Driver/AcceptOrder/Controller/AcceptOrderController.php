<?php
namespace App\Http\Services\Driver\AcceptOrder\Controller;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Driver\AcceptOrder\Logic\AcceptOrderInput;
use App\Http\Services\Driver\AcceptOrder\Logic\AcceptOrderLogic;
use App\Http\Services\Driver\AcceptOrder\Request\AcceptOrderRequest;

class AcceptOrderController extends Controller
{
    public function __invoke(AcceptOrderRequest $request)
    {
        // validate input data and pass it to the service..

        $data = $request->all();
        $data['driverId'] = Auth::id();
        $input = new AcceptOrderInput($data);

        $service = new AcceptOrderLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}