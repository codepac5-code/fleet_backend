<?php
namespace App\Http\Services\Dashboard\VehicleManagement\Pages\CreateVehiclePage\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\VehicleManagement\Pages\CreateVehiclePage\Logic\CreateVehiclePageInput;
use App\Http\Services\Dashboard\VehicleManagement\Pages\CreateVehiclePage\Logic\CreateVehiclePageLogic;
use App\Http\Services\Dashboard\VehicleManagement\CreateOrUpdateVehicle\Logic\CreateOrUpdateVehicleInput;
use App\Http\Services\Dashboard\VehicleManagement\CreateOrUpdateVehicle\Logic\CreateOrUpdateVehicleLogic;
use App\Http\Services\Dashboard\VehicleManagement\CreateOrUpdateVehicle\Request\CreateOrUpdateVehicleRequest;

class CreateVehiclePageController extends Controller
{
    public function __invoke(Request $request)
    {
        // validate input data and pass it to the service..
        $input = new CreateVehiclePageInput($request->all());

        $service = new CreateVehiclePageLogic($input); // call the service's logic

        // execute service and get result..
        return $result = $service->execute();

    }


}