<?php
namespace App\Http\Services\User\FleetWalletPayment\Controller;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\User\FleetWalletPayment\Logic\FleetWalletPaymentInput;
use App\Http\Services\User\FleetWalletPayment\Logic\FleetWalletPaymentLogic;
use App\Http\Services\User\FleetWalletPayment\Request\FleetWalletPaymentRequest;

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