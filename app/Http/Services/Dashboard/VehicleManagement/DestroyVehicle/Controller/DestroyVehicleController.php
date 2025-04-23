<?php
namespace App\Http\Services\Dashboard\VehicleManagement\DestroyVehicle\Controller;

use App\Http\Services\Dashboard\VehicleManagement\DestroyVehicle\Logic\DestroyVehicleInput;
use App\Http\Services\Dashboard\VehicleManagement\DestroyVehicle\Logic\DestroyVehicleLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\VehicleManagement\DestroyVehicle\Request\DestroyVehicleRequest;

class DestroyVehicleController extends Controller
{
    public function __invoke(DestroyVehicleRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new DestroyVehicleInput($request->validated());

        $service = new DestroyVehicleLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}