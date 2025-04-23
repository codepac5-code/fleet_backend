<?php
namespace App\Http\Services\User\UserAddressManagement\AddAddress\Controller;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\User\UserAddressManagement\AddAddress\Logic\AddAddressInput;
use App\Http\Services\User\UserAddressManagement\AddAddress\Logic\AddAddressLogic;
use App\Http\Services\User\UserAddressManagement\AddAddress\Request\AddAddressRequest;

class AddAddressController extends Controller
{
    public function __invoke(AddAddressRequest $request)
    {
        // validate input data and pass it to the service..
        $data = $request->validated();
        unset($request);
        $data['userId'] = Auth::id();
        $input = new AddAddressInput($data);

        $service = new AddAddressLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}
