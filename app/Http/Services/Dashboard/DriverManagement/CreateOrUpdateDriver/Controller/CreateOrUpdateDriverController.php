<?php
namespace App\Http\Services\Dashboard\DriverManagement\CreateOrUpdateDriver\Controller;

use App\Models\Driver;
use App\Http\Controllers\Controller;
use App\Http\Core\Classes\ImageManager;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\DriverManagement\CreateOrUpdateDriver\Logic\CreateOrUpdateDriverInput;
use App\Http\Services\Dashboard\DriverManagement\CreateOrUpdateDriver\Logic\CreateOrUpdateDriverLogic;
use App\Http\Services\Dashboard\DriverManagement\CreateOrUpdateDriver\Request\CreateOrUpdateDriverRequest;

class CreateOrUpdateDriverController extends Controller
{
    public function __invoke(CreateOrUpdateDriverRequest $request)
    {


        $input_data = $request->validated();
        $input_data['has_image'] = $request->hasFile('image');

        // validate input data and pass it to the service..
        $input = new CreateOrUpdateDriverInput($input_data);

        $service = new CreateOrUpdateDriverLogic($input); // call the service's logic

        // execute service and get result..
        return  $service->execute(); // send response..

    }
}
