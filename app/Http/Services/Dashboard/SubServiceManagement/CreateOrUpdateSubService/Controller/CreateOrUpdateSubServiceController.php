<?php
namespace App\Http\Services\Dashboard\SubServiceManagement\CreateOrUpdateSubService\Controller;

use App\Models\SubService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\SubServiceManagement\CreateOrUpdateSubService\Logic\CreateOrUpdateSubServiceInput;
use App\Http\Services\Dashboard\SubServiceManagement\CreateOrUpdateSubService\Logic\CreateOrUpdateSubServiceLogic;
use App\Http\Services\Dashboard\SubServiceManagement\CreateOrUpdateSubService\Request\CreateOrUpdateSubServiceRequest;

class CreateOrUpdateSubServiceController extends Controller
{

    public function __invoke(CreateOrUpdateSubServiceRequest $request)
    {

        // return $request->all();
        $input_data = $request->validated();
        $input_data['has_image'] = $request->hasFile('image');

        // validate input data and pass it to the service..
        $input = new CreateOrUpdateSubServiceInput( $input_data );

        $service = new CreateOrUpdateSubServiceLogic($input); // call the service's logic
        // execute service and get result..
       return $service->execute(); // send response..
    }

}
