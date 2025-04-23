<?php
namespace App\Http\Services\Dashboard\VehicleManagement\CreateOrUpdateVehicle\Controller;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\VehicleManagement\CreateOrUpdateVehicle\Logic\CreateOrUpdateVehicleInput;
use App\Http\Services\Dashboard\VehicleManagement\CreateOrUpdateVehicle\Logic\CreateOrUpdateVehicleLogic;
use App\Http\Services\Dashboard\VehicleManagement\CreateOrUpdateVehicle\Request\CreateOrUpdateVehicleRequest;

class CreateOrUpdateVehicleController extends Controller
{
    public function __invoke(CreateOrUpdateVehicleRequest $request)
    {

        $input_data = $request->validated();
        $input_data['has_image'] = $request->hasFile('image');
        // validate input data and pass it to the service..
        $input = new CreateOrUpdateVehicleInput($input_data);

        $service = new CreateOrUpdateVehicleLogic($input); // call the service's logic

        // execute service and get result..
       return $result = $service->execute();

    }


    // public function to_view(Request $request){
    //     // validate input data and pass it to the service..
    //     $input = new CreateOrUpdateVehicleInput($request->all());

    //     $service = new CreateOrUpdateVehicleLogic($input); // call the service's logic

    //     // execute service and get result..
    //    return $result = $service->view();
    // }
}