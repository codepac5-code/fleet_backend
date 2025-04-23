<?php
namespace App\Http\Services\FleetWalletPayment\Controller;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\FleetWalletPayment\Logic\FleetWalletPaymentInput;
use App\Http\Services\FleetWalletPayment\Logic\FleetWalletPaymentLogic;
use App\Http\Services\FleetWalletPayment\Request\FleetWalletPaymentRequest;

class FleetWalletPaymentController extends Controller
{

    public function __invoke(FleetWalletPaymentRequest $request)
    {
        // validate input data and pass it to the service..

        $data = $request->validated();
        $data['userId']= Auth::id();
        $input = new FleetWalletPaymentInput($data);

        $service = new FleetWalletPaymentLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}