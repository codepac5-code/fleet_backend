<?php
namespace App\Http\Services\User\PaymentService\Controller;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\User\PaymentService\Logic\PaymentServiceInput;
use App\Http\Services\User\PaymentService\Logic\PaymentServiceLogic;
use App\Http\Services\User\PaymentService\Request\PaymentServiceRequest;

class PaymentServiceController extends Controller
{
    public function __invoke(PaymentServiceRequest $request)
    {

        $data = $request->validated();
        $data['userId'] = Auth::guard('user')->id();
        
        $service = new PaymentServiceLogic(); // call the service's logic

        // execute service and get result..
        $result = $service->{$request['paymentName']}($data);

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}