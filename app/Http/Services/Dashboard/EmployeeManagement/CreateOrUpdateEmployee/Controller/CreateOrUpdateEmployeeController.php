<?php
namespace App\Http\Services\Dashboard\EmployeeManagement\CreateOrUpdateEmployee\Controller;



use App\Http\Services\Dashboard\EmployeeManagement\CreateOrUpdateEmployee\Logic\CreateOrUpdateEmployeeInput;
use App\Http\Services\Dashboard\EmployeeManagement\CreateOrUpdateEmployee\Logic\CreateOrUpdateEmployeeLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\EmployeeManagement\CreateOrUpdateEmployee\Request\CreateOrUpdateEmployeeRequest;

class CreateOrUpdateEmployeeController extends Controller
{
    public function __invoke(CreateOrUpdateEmployeeRequest $request)
    {

        $input_data = $request->validated();
        $input_data['has_image'] = $request->hasFile('image');
        // validate input data and pass it to the service..
        $input = new CreateOrUpdateEmployeeInput($input_data);

        $service = new CreateOrUpdateEmployeeLogic($input); // call the service's logic

        // execute service and get result..
        return $service->execute();

        // return SendResponse::sendSuccessResponse($result); // send response..
    }
}