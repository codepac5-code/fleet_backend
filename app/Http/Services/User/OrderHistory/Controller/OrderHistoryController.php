<?php
namespace App\Http\Services\User\OrderHistory\Controller;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\User\OrderHistory\Logic\OrderHistoryInput;
use App\Http\Services\User\OrderHistory\Logic\OrderHistoryLogic;

class OrderHistoryController extends Controller
{
    public function __invoke()
    {
        
        // validate input data and pass it to the service..
        $input = new OrderHistoryInput(['userId' => Auth::id()]);

        $service = new OrderHistoryLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}