<?php
namespace App\Http\Services\Dashboard\VehicleManagement\BulkActionVehicle\Controller;

use App\Http\Services\Dashboard\VehicleManagement\BulkActionVehicle\Logic\BulkActionVehicleInput;
use App\Http\Services\Dashboard\VehicleManagement\BulkActionVehicle\Logic\BulkActionVehicleLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\VehicleManagement\BulkActionVehicle\Request\BulkActionVehicleRequest;

class BulkActionVehicleController extends Controller
{
    public function __invoke(BulkActionVehicleRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new BulkActionVehicleInput($request->validated());

        $service = new BulkActionVehicleLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}