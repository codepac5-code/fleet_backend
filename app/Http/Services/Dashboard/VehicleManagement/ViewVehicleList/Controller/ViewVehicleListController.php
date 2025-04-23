<?php
namespace App\Http\Services\Dashboard\VehicleManagement\ViewVehicleList\Controller;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\VehicleManagement\ViewVehicleList\Logic\ViewVehicleListInput;
use App\Http\Services\Dashboard\VehicleManagement\ViewVehicleList\Logic\ViewVehicleListLogic;

class ViewVehicleListController extends Controller
{
    public function __invoke(Request $request)
    {
        // validate input data and pass it to the service..
        $input = new ViewVehicleListInput($request->all());

        $service = new ViewVehicleListLogic($input); // call the service's logic

        // execute service and get result..
        return $result = $service->execute();

        // return SendResponse::sendSuccessResponse($result); // send response..
    }
}