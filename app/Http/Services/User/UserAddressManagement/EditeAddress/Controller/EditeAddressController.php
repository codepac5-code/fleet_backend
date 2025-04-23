<?php
namespace App\Http\Services\User\UserAddressManagement\EditeAddress\Controller;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\User\UserAddressManagement\EditeAddress\Logic\EditeAddressInput;
use App\Http\Services\User\UserAddressManagement\EditeAddress\Logic\EditeAddressLogic;

class EditeAddressController extends Controller
{
    public function __invoke(Request $request)
    {
        // validate input data and pass it to the service..
        $input = new EditeAddressInput($request->all());

        $service = new EditeAddressLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}
