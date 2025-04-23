<?php
namespace App\Http\Services\User\MakeOrder\Controller;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\User\MakeOrder\Logic\MakeOrderInput;
use App\Http\Services\User\MakeOrder\Logic\MakeOrderLogic;
use App\Http\Services\User\MakeOrder\Request\MakeOrderRequest;

class MakeOrderController extends Controller
{
    public function __invoke(MakeOrderRequest $request)
    {
        $data = $request->all();
        $data['userId'] = Auth::id();
        
        
        // validate input data and pass it to the service..
        $input = new MakeOrderInput($data);

        $service = new MakeOrderLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}