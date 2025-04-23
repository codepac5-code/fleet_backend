<?php
namespace App\Http\Services\Dashboard\ServiceManagement\CreateOrUpdateService\Controller;

use App\Models\Service;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\ServiceManagement\CreateOrUpdateService\Logic\CreateOrUpdateServiceInput;
use App\Http\Services\Dashboard\ServiceManagement\CreateOrUpdateService\Logic\CreateOrUpdateServiceLogic;
use App\Http\Services\Dashboard\ServiceManagement\CreateOrUpdateService\Request\CreateOrUpdateServiceRequest;

class CreateOrUpdateServiceController extends Controller
{

public function __invoke(CreateOrUpdateServiceRequest $request)
    {
        $input_data = $request->validated();
        $input_data['has_image'] = $request->hasFile('image');
        // validate input data and pass it to the service..
        $input = new CreateOrUpdateServiceInput( $input_data );


        $service = new CreateOrUpdateServiceLogic($input); // call the service's logic

        // execute service and get result..
        return $service->execute(); // send response..
    }

}