<?php
namespace App\Http\Services\Dashboard\OfficeManagement\ViewVehicleByOffice\Controller;

use App\Http\Services\Dashboard\OfficeManagement\ViewVehicleByOffice\Logic\ViewVehicleByOfficeInput;
use App\Http\Services\Dashboard\OfficeManagement\ViewVehicleByOffice\Logic\ViewVehicleByOfficeLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\OfficeManagement\ViewVehicleByOffice\Request\ViewVehicleByOfficeRequest;

class ViewVehicleByOfficeController extends Controller
{
    public function __invoke(ViewVehicleByOfficeRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new ViewVehicleByOfficeInput($request->validated());

        $service = new ViewVehicleByOfficeLogic($input); // call the service's logic

        // execute service and get result..
        return $service->execute();

       // return SendResponse::sendSuccessResponse($result); // send response..
    }
}