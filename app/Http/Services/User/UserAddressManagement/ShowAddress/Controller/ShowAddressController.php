<?php
namespace App\Http\Services\User\UserAddressManagement\ShowAddress\Controller;


use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\User\UserAddressManagement\ShowAddress\Logic\ShowAddressInput;
use App\Http\Services\User\UserAddressManagement\ShowAddress\Logic\ShowAddressLogic;

class ShowAddressController extends Controller
{
    public function __invoke(Request $request)
    {
        // validate input data and pass it to the service..
        $data['userId'] = Auth::id();
        $input = new ShowAddressInput($data);

        $service = new ShowAddressLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}
