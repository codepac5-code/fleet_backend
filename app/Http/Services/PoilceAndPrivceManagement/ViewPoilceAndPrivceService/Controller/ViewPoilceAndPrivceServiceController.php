<?php
namespace App\Http\Services\PoilceAndPrivceManagement\ViewPoilceAndPrivceService\Controller;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\PoilceAndPrivceManagement\ViewPoilceAndPrivceService\Logic\ViewPoilceAndPrivceServiceInput;
use App\Http\Services\PoilceAndPrivceManagement\ViewPoilceAndPrivceService\Logic\ViewPoilceAndPrivceServiceLogic;

class ViewPoilceAndPrivceServiceController extends Controller
{
    public function __invoke(Request $request)
    {
        // validate input data and pass it to the service..
        $input = new ViewPoilceAndPrivceServiceInput($request->validate());

        $service = new ViewPoilceAndPrivceServiceLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}
