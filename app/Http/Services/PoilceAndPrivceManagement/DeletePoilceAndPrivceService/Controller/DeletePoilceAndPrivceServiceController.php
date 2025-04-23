<?php
namespace App\Http\Services\PoilceAndPrivceManagement\DeletePoilceAndPrivceService\Controller;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\PoilceAndPrivceManagement\DeletePoilceAndPrivceService\Logic\DeletePoilceAndPrivceServiceInput;
use App\Http\Services\PoilceAndPrivceManagement\DeletePoilceAndPrivceService\Logic\DeletePoilceAndPrivceServiceLogic;

class DeletePoilceAndPrivceServiceController extends Controller
{
    public function __invoke(Request $request)
    {
        // validate input data and pass it to the service..
        $input = new DeletePoilceAndPrivceServiceInput($request->validate());

        $service = new DeletePoilceAndPrivceServiceLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}
