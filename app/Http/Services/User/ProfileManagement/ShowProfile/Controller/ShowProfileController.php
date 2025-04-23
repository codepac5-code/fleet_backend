<?php
namespace App\Http\Services\User\ProfileManagement\ShowProfile\Controller;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\User\ProfileManagement\ShowProfile\Logic\ShowProfileInput;
use App\Http\Services\User\ProfileManagement\ShowProfile\Logic\ShowProfileLogic;

class ShowProfileController extends Controller
{
    public function __invoke(Request $request)
    {
        // validate input data and pass it to the service..
        $input = new ShowProfileInput(Auth::user());

        $service = new ShowProfileLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}
