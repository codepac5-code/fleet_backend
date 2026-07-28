<?php
namespace App\Http\Services\Dashboard\CommissionManagement\UpdateOfficeCommissions\Controller;

use App\Http\Services\Dashboard\CommissionManagement\UpdateOfficeCommissions\Logic\UpdateOfficeCommissionsInput;
use App\Http\Services\Dashboard\CommissionManagement\UpdateOfficeCommissions\Logic\UpdateOfficeCommissionsLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\CommissionManagement\UpdateOfficeCommissions\Request\UpdateOfficeCommissionsRequest;

class UpdateOfficeCommissionsController extends Controller
{
    public function __invoke(UpdateOfficeCommissionsRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new UpdateOfficeCommissionsInput($request->all());

        $service = new UpdateOfficeCommissionsLogic($input); // call the service's logic

        // execute service and get result..
        return $service->execute(); // send response..
    }
}
