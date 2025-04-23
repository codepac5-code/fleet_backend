<?php
namespace App\Http\Services\User\UserAddressManagement\ViewAddress\Controller;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\User\UserAddressManagement\ViewAddress\Logic\ViewAddressInput;
use App\Http\Services\User\UserAddressManagement\ViewAddress\Logic\ViewAddressLogic;

class ViewAddressController extends Controller
{
    public function __invoke(Request $request)
    {
        // validate input data and pass it to the service..
        $input = new ViewAddressInput($request->all());

        $service = new ViewAddressLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}
