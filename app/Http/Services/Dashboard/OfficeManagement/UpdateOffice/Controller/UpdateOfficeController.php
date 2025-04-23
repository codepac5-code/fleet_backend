<?php
namespace App\Http\Services\Dashboard\OfficeManagement\UpdateOffice\Controller;

use App\Http\Services\Dashboard\OfficeManagement\UpdateOffice\Logic\UpdateOfficeInput;
use App\Http\Services\Dashboard\OfficeManagement\UpdateOffice\Logic\UpdateOfficeLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\OfficeManagement\UpdateOffice\Request\UpdateOfficeRequest;

class UpdateOfficeController extends Controller
{
    public function __invoke(UpdateOfficeRequest $request)
    {

        $input_data = $request->validated();
        $input_data['has_image'] = $request->hasFile('logo');
        // validate input data and pass it to the service..
        $input = new UpdateOfficeInput($input_data);

        $service = new UpdateOfficeLogic($input); // call the service's logic

        // execute service and get result..
        return  $service->execute(); // send response..
    }
}