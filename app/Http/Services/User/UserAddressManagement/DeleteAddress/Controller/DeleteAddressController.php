<?php
namespace App\Http\Services\User\UserAddressManagement\DeleteAddress\Controller;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\User\UserAddressManagement\DeleteAddress\Logic\DeleteAddressInput;
use App\Http\Services\User\UserAddressManagement\DeleteAddress\Logic\DeleteAddressLogic;
use App\Http\Services\User\UserAddressManagement\DeleteAddress\Request\DeleteAddressRequest;

class DeleteAddressController extends Controller
{
    public function __invoke(DeleteAddressRequest $request)
    {
        // validate input data and pass it to the service..
        $data = $request->validated();
        unset($request);
        $data['userId'] = Auth::id();
        $input = new DeleteAddressInput($data);

        $service = new DeleteAddressLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}
